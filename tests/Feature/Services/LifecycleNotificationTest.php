<?php

namespace Tests\Feature\Services;

use App\Jobs\SendStoredNotification;
use App\Models\{FollowUser, LiveVideo, LiveVideoItem, LiveVideoItemFavorite, LiveVideoUser, Notification, Order, SellerSubmission, VideoComment};
use App\Models\User\User;
use App\Services\{FCMService, LifecycleNotificationService, NotificationService, OrderService};
use Illuminate\Support\Facades\{DB, Queue};
use Laravel\Passport\Passport;
use Tests\Support\NotificationTestCase;

class LifecycleNotificationTest extends NotificationTestCase
{
    private function user(array $attributes = []): User
    {
        return User::create($attributes + ['name' => 'Recipient', 'is_active' => 1, 'app_lang' => 'en']);
    }

    private function entity(string $kind, ?string $status, User $user)
    {
        if ($kind === 'auction') {
            return LiveVideo::create(['user_id' => $user->id, 'status' => $status]);
        }
        if ($kind === 'seller') {
            return SellerSubmission::create(['user_id' => $user->id, 'partner_id' => $this->user()->id,
                'sheep_type' => 'Sheep', 'age' => '1', 'expected_price' => 100, 'status' => $status]);
        }
        return Order::create(['buyer_id' => $user->id, 'live_video_id' => LiveVideo::create([])->id,
            'status' => $status, 'payment_status' => 'unpaid']);
    }

    public static function transitions(): array
    {
        $cases = [];
        foreach (['auction' => LifecycleNotificationService::AUCTION_STATUSES,
            'seller' => LifecycleNotificationService::SELLER_STATUSES, 'order' => LifecycleNotificationService::ORDER_STATUSES] as $kind => $statuses) {
            foreach ($statuses as $from) {
                foreach ($statuses as $to) {
                    if ($from !== $to) { $cases[$kind.':'.$from.'->'.$to] = [$kind, $from, $to]; }
                }
            }
        }
        foreach (LifecycleNotificationService::AUCTION_STATUSES as $to) {
            $cases['auction:null->'.$to] = ['auction', null, $to];
        }
        return $cases;
    }

    /** @dataProvider transitions */
    public function test_actual_supported_transitions_and_repeated_saves(string $kind, ?string $from, string $to): void
    {
        $recipient = $this->user();
        $entity = $this->entity($kind, $from, $recipient);
        $this->assertSame(0, Notification::count(), 'Creation is not a transition.');
        $stale = $entity->fresh();
        $entity->update(['status' => $to]);
        $entity->update(['status' => $to]);
        $stale->update(['status' => $to]);
        $this->assertSame(1, Notification::count());
        $receipt = Notification::first();
        $this->assertSame($recipient->id, $receipt->user_id);
        $this->assertSame($from, $receipt->data['from_status']);
        $this->assertSame($to, $receipt->data['to_status']);
        $this->assertSame($entity->id, $receipt->data['entity_id']);
        $this->assertSame('FLUTTER_NOTIFICATION_CLICK', $receipt->data['click_action']);
        $this->assertSame('notification', $receipt->data['screen']);
        $this->assertNotEmpty($receipt->data['occurred_at']);
        $this->assertSame($receipt->data['entity_type'], $receipt->data['navigation']['entity_type']);
        Queue::assertPushed(SendStoredNotification::class, 1);
    }

    public function test_legitimate_repeated_transition_gets_new_history(): void
    {
        $entity = $this->entity('seller', 'pending', $this->user());
        foreach (['needs edit', 'pending', 'needs edit'] as $status) { $entity->update(['status' => $status]); }
        $this->assertSame(3, Notification::count());
        $this->assertSame(3, Notification::get()->pluck('event_key')->unique()->count());
    }

    public function test_auction_relationships_are_targeted_and_deduplicated(): void
    {
        $owner = $this->user();
        $seller = $this->user();
        $winner = $this->user();
        $follower = $this->user();
        $favorite = $this->user();
        $viewer = $this->user();
        $bidder = $this->user();
        $partner = $this->user();
        $unrelated = $this->user();
        $inactive = $this->user(['is_active' => 0]);
        $deleted = $this->user(); $deleted->delete();
        $auction = LiveVideo::create(['user_id' => $owner->id, 'partner_id' => $partner->id, 'status' => 'pending']);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'seller_id' => $seller->id, 'user_finished_id' => $winner->id]);
        foreach ([$follower, $seller, $owner, $inactive, $deleted] as $user) {
            FollowUser::create(['user_id' => $user->id, 'follow_id' => $owner->id]);
        }
        LiveVideoItemFavorite::create(['user_id' => $favorite->id, 'live_video_item_id' => $item->id]);
        LiveVideoUser::create(['user_id' => $viewer->id, 'live_video_id' => $auction->id]);
        VideoComment::create(['user_id' => $bidder->id, 'live_video_item_id' => $item->id, 'video_id' => $auction->id, 'comment' => '100']);
        // A comment referring to an item in another auction must not grant eligibility.
        $other = LiveVideo::create([]);
        $otherItem = LiveVideoItem::create(['live_video_id' => $other->id]);
        VideoComment::create(['user_id' => $unrelated->id, 'live_video_item_id' => $otherItem->id, 'video_id' => $auction->id]);
        $auction->update(['status' => 'start']);
        $this->assertEqualsCanonicalizing([$owner->id, $seller->id, $winner->id, $follower->id, $favorite->id, $viewer->id, $bidder->id, $partner->id],
            Notification::pluck('user_id')->all());
    }

    public function test_transferred_item_does_not_add_its_private_seller_or_favorites_to_old_auction(): void
    {
        $auction = LiveVideo::create(['status' => 'pending']);
        $seller = $this->user(); $favorite = $this->user();
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'seller_id' => $seller->id]);
        LiveVideoItemFavorite::create(['user_id' => $favorite->id, 'live_video_item_id' => $item->id]);
        LiveVideoItem::create(['live_video_id' => LiveVideo::create([])->id, 'transferred_from_item_id' => $item->id]);
        $auction->update(['status' => 'end']);
        $this->assertSame(0, Notification::count());
    }

    public static function locales(): array { return [['en', 'ar'], ['ar', 'en']]; }

    /** @dataProvider locales */
    public function test_recipient_localization_independent_of_admin(string $admin, string $language): void
    {
        app()->setLocale($admin);
        $user = $this->user(['app_lang' => $language]);
        foreach (['auction', 'seller', 'order'] as $kind) {
            $entity = $this->entity($kind, 'pending', $user);
            $entity->update(['status' => $kind === 'auction' ? 'start' : ($kind === 'seller' ? 'approved' : 'shipping')]);
            $receipt = Notification::latest('id')->first();
            $this->assertSame(trans('notifications.'.$receipt->type.'.title', [], $language), $receipt->title);
            $this->assertStringNotContainsString('notifications.', $receipt->description);
            $status = trans('notifications.statuses.'.$receipt->data['to_status'], [], $language);
            if ($kind !== 'auction') { $this->assertStringContainsString($status, $receipt->description); }
        }
        $this->assertSame($admin, app()->getLocale());
    }

    public function test_null_auction_status_and_unknown_language_fallback(): void
    {
        $user = $this->user(['app_lang' => null]);
        $auction = LiveVideo::create(['user_id' => $user->id]);
        $auction->update(['status' => 'start']);
        $this->assertSame('بدأ المزاد', Notification::first()->title);
        $this->assertNull(Notification::first()->data['from_status']);
    }

    public function test_payment_changes_use_actual_buyer_and_preserve_workflow(): void
    {
        $buyer = $this->user(); $this->user();
        $order = $this->entity('order', 'pending', $buyer);
        // Existing service automatically changes paid+pending to confirmed.
        OrderService::updateOrderStatus($order, 'paid', 'pending', false);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertEqualsCanonicalizing(['order_status_changed', 'order_payment_status_changed'], Notification::pluck('type')->all());
        $this->assertEquals([$buyer->id], Notification::pluck('user_id')->unique()->all());
        $order->update(['payment_status' => 'unpaid']);
        $this->assertSame('unpaid', Notification::latest('id')->first()->data['to_status']);
        $order->update(['payment_status' => 'unpaid']);
        $this->assertSame(3, Notification::count());
    }

    public function test_rollback_discards_history_and_external_side_effects(): void
    {
        $entity = $this->entity('seller', 'pending', $this->user());
        DB::beginTransaction();
        $entity->update(['status' => 'approved']);
        $this->assertSame(1, Notification::count());
        Queue::assertNothingPushed();
        DB::rollBack();
        $this->assertSame(0, Notification::count());
        Queue::assertNothingPushed();
        $entity->fresh()->update(['status' => 'approved']);
        Queue::assertPushed(SendStoredNotification::class, 1);
    }

    public function test_nested_commit_waits_for_outer_commit(): void
    {
        $entity = $this->entity('order', 'pending', $this->user());
        DB::beginTransaction();
        DB::transaction(function () use ($entity) { $entity->update(['status' => 'shipping']); });
        Queue::assertNothingPushed();
        DB::commit();
        Queue::assertPushed(SendStoredNotification::class, 1);
    }

    public function test_explicit_event_replay_keeps_original_history_and_read_timestamp(): void
    {
        $user = $this->user();
        $first = NotificationService::notifyOnce($user->id, 'stable-event', 'order_status_changed', 'Title', 'Body', ['order_id' => 1]);
        $first->update(['read_at' => now()]);
        $createdAt = $first->created_at->toISOString();
        $readAt = $first->read_at->toISOString();
        $second = NotificationService::notifyOnce($user->id, 'stable-event', 'order_status_changed', 'Replacement', 'Replacement', []);
        $this->assertSame($first->id, $second->id);
        $this->assertSame('Title', $second->title);
        $this->assertSame($createdAt, $second->created_at->toISOString());
        $this->assertSame($readAt, $second->read_at->toISOString());
        Queue::assertPushed(SendStoredNotification::class, 1);
    }

    public function test_push_failure_retry_and_duplicate_jobs_keep_one_receipt(): void
    {
        $user = $this->user(['fcm_token' => 'recipient-token', 'app_lang' => 'ar']);
        $entity = $this->entity('seller', 'pending', $user);
        $entity->update(['status' => 'rejected']);
        $receipt = Notification::first();
        $mock = \Mockery::mock(FCMService::class);
        $mock->shouldReceive('sendToDevice')->once()->andReturn(['success' => false]);
        $this->app->instance(FCMService::class, $mock);
        $job = new SendStoredNotification($receipt->id);
        try { $job->handle(); $this->fail('Expected a retryable delivery failure.'); }
        catch (\RuntimeException $e) { $this->assertNull($receipt->fresh()->push_completed_at); }
        $mock->shouldReceive('sendToDevice')->once()->withArgs(function ($token, $title, $body, $data) use ($receipt) {
            $this->assertSame('recipient-token', $token);
            $this->assertSame($receipt->title, $title);
            $this->assertSame($receipt->description, $body);
            $this->assertSame((string) $receipt->id, $data['notification_id']);
            $this->assertSame('seller_request_status_changed', $data['type']);
            $this->assertSame('FLUTTER_NOTIFICATION_CLICK', $data['click_action']);
            $this->assertSame('notification', $data['screen']);
            $this->assertSame((string) $receipt->data['seller_submission_id'], $data['seller_submission_id']);
            $this->assertIsArray(json_decode($data['navigation'], true));
            return true;
        })->andReturn(['success' => true]);
        $job->handle();
        $job->handle();
        $this->assertNotNull($receipt->fresh()->push_completed_at);
        $this->assertSame(1, Notification::count());
    }

    public function test_recovery_scheduler_only_requeues_unfinished_lifecycle_receipts(): void
    {
        $user = $this->user();
        $receipt = NotificationService::notifyOnce($user->id, 'orphan', 'order_status_changed', 'T', 'B', []);
        NotificationService::notify($user->id, 'identity_verified', 'Legacy', 'Body');
        Queue::fake();
        $this->travel(2)->minutes();
        $this->artisan('notifications:retry-pushes')->assertExitCode(0);
        Queue::assertPushed(SendStoredNotification::class, 1);
        (new SendStoredNotification($receipt->id))->handle(); // No token is terminal; inbox stays.
        Queue::fake();
        $this->artisan('notifications:retry-pushes')->assertExitCode(0);
        Queue::assertNothingPushed();
    }

    public function test_private_status_notifications_cannot_be_read_by_other_users(): void
    {
        $buyer = $this->user();
        $entity = $this->entity('order', 'pending', $buyer);
        $entity->update(['status' => 'delivered']);
        $receipt = Notification::first();
        Passport::actingAs($this->user(), [], 'api');
        $this->getJson('/api/user/notifications')->assertOk()->assertJsonCount(0, 'data');
        $this->postJson('/api/user/notifications/'.$receipt->id.'/mark-read')->assertJsonPath('success', false);
        $this->postJson('/api/user/notifications/mark-all-read');
        $this->assertNull($receipt->fresh()->read_at);
        Passport::actingAs($buyer, [], 'api');
        $this->getJson('/api/user/notifications')->assertOk()->assertJsonPath('data.0.data.order_id', $entity->id)
            ->assertJsonPath('data.0.data.navigation.entity_type', 'order')->assertJsonPath('data.0.is_read', false)
            ->assertJsonStructure(['data' => [['created_at', 'time', 'title', 'description', 'type']], 'pagination', 'unread_count']);
    }

    public function test_non_owner_cannot_trigger_auction_lifecycle_notifications(): void
    {
        $auction = $this->entity('auction', 'pending', $this->user());
        Passport::actingAs($this->user(), [], 'api');
        $controller = app(\App\Http\Controllers\api\Live\LiveVideoController::class);
        $this->assertFalse($controller->start($auction->id)->getData()->success);
        $this->assertFalse($controller->end($auction->id)->getData()->success);
        $this->assertSame('pending', $auction->fresh()->status);
        $this->assertSame(0, Notification::count());
    }

    public function test_transaction_retry_reloads_state_and_discards_rolled_back_receipts(): void
    {
        $entity = $this->entity('seller', 'pending', $this->user());
        $attempts = 0;
        DB::transaction(function () use ($entity, &$attempts) {
            $entity->fresh()->update(['status' => 'approved']);
            if (++$attempts === 1) { throw new \PDOException('Deadlock found when trying to get lock', 40001); }
        }, 3);
        $this->assertSame(2, $attempts);
        $this->assertSame(1, Notification::count());
        Queue::assertPushed(SendStoredNotification::class, 1);
    }

    public function test_existing_reminder_timing_pending_state_localization_and_rollback(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-28 12:00:00'));
        app()->setLocale('en');
        $user = $this->user(['app_lang' => 'ar']);
        foreach ([0, 60, 61, -1] as $minutes) {
            $at = now()->addMinutes($minutes);
            LiveVideo::create(['user_id' => $user->id, 'status' => 'pending',
                'date_start_at' => $at->toDateString(), 'time_start_at' => $at->toTimeString()]);
        }
        DB::beginTransaction();
        $this->artisan('notifications:upcoming-auction-reminders')->assertExitCode(0);
        $this->assertSame(2, Notification::count());
        $this->assertSame('مزاد يبدأ قريباً', Notification::first()->title);
        Queue::assertNothingPushed();
        DB::rollBack();
        $this->assertSame(0, Notification::count());
        $this->assertSame(0, LiveVideo::whereNotNull('upcoming_reminder_sent_at')->count());
        $this->artisan('notifications:upcoming-auction-reminders');
        $this->artisan('notifications:upcoming-auction-reminders');
        $this->assertSame(2, Notification::count());
        Queue::assertPushed(SendStoredNotification::class, 2);
    }

    public function test_scheduled_start_and_end_use_the_same_inbox_once(): void
    {
        if (! $this->mysqlConfig) { $this->markTestSkipped('Existing scheduler SQL uses MySQL TIMESTAMP().'); }
        $this->travelTo(\Carbon\Carbon::parse('2026-10-28 12:00:00'));
        $auction = LiveVideo::create(['user_id' => $this->user()->id, 'status' => 'pending',
            'date_start_at' => '2026-10-28', 'time_start_at' => '11:00:00',
            'date_end_at' => '2026-10-28', 'time_end_at' => '13:00:00']);
        $firebase = \Mockery::mock(\App\Http\Controllers\FirebaseController::class);
        $firebase->shouldReceive('ChangeLiveStatus')->once()->with($auction->id, 'start');
        $firebase->shouldReceive('removeLive')->once()->with($auction->id);
        $this->app->instance(\App\Http\Controllers\FirebaseController::class, $firebase);
        $this->artisan('recordedvideostart:check');
        $this->artisan('recordedvideostart:check');
        $this->assertSame('start', $auction->fresh()->status);
        $this->assertSame(1, Notification::where('type', 'auction_started')->count());
        $this->travel(2)->hours();
        $this->artisan('livevideoEnd:check');
        $this->artisan('livevideoEnd:check');
        $this->assertSame('end', $auction->fresh()->status);
        $this->assertSame(1, Notification::where('type', 'auction_ended')->count());
        Queue::assertPushed(SendStoredNotification::class, 2);
    }

    public function test_seller_dashboard_decisions_notify_only_the_submitter(): void
    {
        $seller = $this->user(['app_lang' => 'ar']);
        $entity = $this->entity('seller', 'pending', $seller);
        \Illuminate\Support\Facades\Auth::guard('admin')->setUser(new \App\Models\Admin(['id' => 1, 'type' => 'admin']));
        $controller = app(\App\Http\Controllers\Dashboard\SellerSubmissionController::class);
        $controller->approve($entity->id);
        $controller->approve($entity->id);
        $controller->reject($entity->id, \Illuminate\Http\Request::create('/', 'POST', ['review_note' => 'Review']));
        $controller->request_edit($entity->id, \Illuminate\Http\Request::create('/', 'POST', ['review_note' => 'Please edit']));
        $this->assertSame(3, Notification::count());
        $this->assertEquals([$seller->id], Notification::pluck('user_id')->unique()->all());
        $this->assertEquals(['approved', 'rejected', 'needs edit'], Notification::get()->pluck('data.to_status')->all());
    }

    public function test_other_partner_cannot_decide_seller_request_or_receive_its_notification(): void
    {
        $entity = $this->entity('seller', 'pending', $this->user());
        \Illuminate\Support\Facades\Auth::guard('admin')->setUser(new \App\Models\Admin(['id' => 1, 'type' => 'partner', 'user_id' => $this->user()->id]));
        try {
            app(\App\Http\Controllers\Dashboard\SellerSubmissionController::class)->approve($entity->id);
            $this->fail('Another partner approved the seller request.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame('pending', $entity->fresh()->status);
        $this->assertSame(0, Notification::count());
    }

    public function test_private_receipt_without_active_recipient_is_never_pushed(): void
    {
        $user = $this->user(['fcm_token' => 'old-token']);
        $entity = $this->entity('seller', 'pending', $user);
        $entity->update(['status' => 'approved']);
        $user->delete();
        $fcm = \Mockery::mock(FCMService::class);
        $fcm->shouldNotReceive('sendToDevice');
        $this->app->instance(FCMService::class, $fcm);
        $receipt = Notification::first();
        (new SendStoredNotification($receipt->id))->handle();
        $this->assertNotNull($receipt->fresh()->push_completed_at);
    }

    public function test_delivery_migration_preserves_legacy_history_on_roundtrip(): void
    {
        if (! $this->mysqlConfig) { $this->markTestSkipped('SQLite column removal requires a DBAL version compatible with Laravel 8.'); }
        $receipt = NotificationService::notify($this->user()->id, 'identity_verified', 'Existing title', 'Existing description');
        $before = $receipt->getAttributes();
        (new \AddNotificationDeliveryLedger())->down();
        (new \AddNotificationDeliveryLedger())->up();
        $after = $receipt->fresh()->getAttributes();
        foreach ($before as $field => $value) { $this->assertEquals($value, $after[$field]); }
        $this->assertNull($receipt->fresh()->event_key);
    }

    public function test_delivery_schema_rollback_refuses_to_discard_used_deduplication_keys(): void
    {
        NotificationService::notifyOnce($this->user()->id, 'event', 'order_status_changed', 'T', 'B', []);
        $this->expectException(\RuntimeException::class);
        (new \AddNotificationDeliveryLedger())->down();
    }

    public function test_dashboard_broadcast_manager_cannot_read_or_delete_private_receipts(): void
    {
        $entity = $this->entity('seller', 'pending', $this->user());
        $entity->update(['status' => 'rejected']);
        $receipt = Notification::first();
        $broadcast = Notification::create(['title' => 'Broadcast', 'description' => 'Public']);
        $admin = \Mockery::mock(\App\Models\Admin::class)->makePartial();
        $admin->shouldReceive('can')->andReturn(true);
        \Illuminate\Support\Facades\Auth::guard('admin')->setUser($admin);
        $controller = app(\App\Http\Controllers\Dashboard\NotificationsController::class);
        $response = $controller->get_data(\Illuminate\Http\Request::create('/', 'POST'))->getData(true);
        $this->assertEquals([$broadcast->id], array_column($response['data'], 'id'));
        foreach (['view', 'destroy'] as $action) {
            try {
                $controller->$action($receipt->id);
                $this->fail('Private inbox receipt was exposed through broadcast management.');
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertTrue(true);
            }
        }
        $this->assertNotNull($receipt->fresh());
    }
}
