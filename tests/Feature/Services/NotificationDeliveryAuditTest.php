<?php

namespace Tests\Feature\Services;

use App\Jobs\SendStoredNotification;
use App\Models\{LiveVideo, Notification, Order, SellerSubmission};
use App\Models\User\User;
use App\Services\{FCMService, LifecycleNotificationService};
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\{QueueManager, WorkerOptions};
use Illuminate\Queue\Connectors\{DatabaseConnector, SyncConnector};
use Illuminate\Support\Facades\{DB, Queue, Schema};
use Laravel\Passport\Passport;
use Tests\Support\NotificationTestCase;

/** Real Laravel queue / Firebase SDK payload boundary; never a network send. */
class NotificationDeliveryAuditTest extends NotificationTestCase
{
    private $hasJobsTable = false;

    private function realQueue(string $driver): void
    {
        config(['queue.default' => $driver, 'queue.failed.driver' => null,
            'queue.connections.notification_audit' => ['driver' => 'database', 'connection' => 'notifications_test',
                'table' => 'notification_audit_jobs', 'queue' => 'notification-audit', 'retry_after' => 90, 'after_commit' => true]]);
        $manager = new QueueManager($this->app);
        $manager->addConnector('sync', function () { return new SyncConnector(); });
        $manager->addConnector('database', function () { return new DatabaseConnector($this->app['db']); });
        Queue::swap($manager);
    }

    private function user(string $token = 'test-token', string $language = 'en'): User
    {
        return User::create(['name' => 'Audit recipient', 'is_active' => 1, 'fcm_token' => $token, 'app_lang' => $language]);
    }

    private function sellerRequest(User $user): SellerSubmission
    {
        return SellerSubmission::create(['user_id' => $user->id, 'partner_id' => $user->id, 'sheep_type' => 'Sheep',
            'age' => '1', 'expected_price' => 100, 'status' => 'pending']);
    }

    public function test_recovery_continues_after_one_synchronous_push_fails(): void
    {
        $failed = $this->sellerRequest($this->user('bad-device'));
        $valid = $this->sellerRequest($this->user('good-device'));
        $failed->update(['status' => 'approved']);
        $valid->update(['status' => 'approved']);
        $this->travel(2)->minutes();
        $this->realQueue('sync');
        $fcm = \Mockery::mock(FCMService::class);
        $fcm->shouldReceive('sendToDevice')->once()->with('bad-device', \Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andReturn(['success' => false]);
        $fcm->shouldReceive('sendToDevice')->once()->with('good-device', \Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andReturn(['success' => true]);
        $this->app->instance(FCMService::class, $fcm);
        $this->artisan('notifications:retry-pushes')->assertExitCode(0);
        $this->assertNull(Notification::where('user_id', $failed->user_id)->first()->push_completed_at);
        $this->assertNotNull(Notification::where('user_id', $valid->user_id)->first()->push_completed_at);
        $this->assertSame(2, Notification::count());
    }

    public function test_real_sync_dispatch_waits_for_outer_commit_and_discards_rollback(): void
    {
        $entity = $this->sellerRequest($this->user());
        $this->realQueue('sync');
        $sends = 0;
        $fcm = \Mockery::mock(FCMService::class);
        $fcm->shouldReceive('sendToDevice')->once()->andReturnUsing(function () use (&$sends) {
            $sends++;
            return ['success' => true];
        });
        $this->app->instance(FCMService::class, $fcm);
        DB::beginTransaction();
        $entity->update(['status' => 'approved']);
        $this->assertSame(0, $sends);
        DB::rollBack();
        $this->assertSame(0, Notification::count());
        DB::beginTransaction();
        $entity->fresh()->update(['status' => 'approved']);
        $this->assertSame(0, $sends);
        DB::commit();
        $this->assertSame(1, $sends);
        $this->assertNotNull(Notification::first()->push_completed_at);
    }

    public function test_database_queue_worker_releases_failure_then_retries_without_new_history(): void
    {
        Schema::create('notification_audit_jobs', function (Blueprint $t) {
            $t->bigIncrements('id'); $t->string('queue')->index(); $t->longText('payload');
            $t->unsignedTinyInteger('attempts'); $t->unsignedInteger('reserved_at')->nullable();
            $t->unsignedInteger('available_at'); $t->unsignedInteger('created_at');
        });
        $this->hasJobsTable = true;
        $this->realQueue('notification_audit');
        $entity = $this->sellerRequest($this->user());
        DB::beginTransaction();
        $entity->update(['status' => 'rejected']);
        $this->assertSame(0, DB::table('notification_audit_jobs')->count());
        DB::commit();
        $this->assertSame(1, DB::table('notification_audit_jobs')->count());
        $receipt = Notification::first();
        $before = $receipt->getAttributes();
        $fcm = \Mockery::mock(FCMService::class);
        $fcm->shouldReceive('sendToDevice')->once()->ordered()->andReturn(['success' => false]);
        $fcm->shouldReceive('sendToDevice')->once()->ordered()->andReturn(['success' => true]);
        $this->app->instance(FCMService::class, $fcm);
        $worker = $this->app->make('queue.worker');
        $options = new WorkerOptions('notification-audit', 0, 128, 60, 0, 5);
        $worker->runNextJob('notification_audit', 'notification-audit', $options);
        $this->assertNull($receipt->fresh()->push_completed_at);
        $queued = DB::table('notification_audit_jobs')->first();
        $this->assertSame(1, (int) $queued->attempts);
        $this->assertGreaterThanOrEqual(now()->addSeconds(59)->timestamp, (int) $queued->available_at);
        $this->travel(61)->seconds();
        $worker->runNextJob('notification_audit', 'notification-audit', $options);
        $this->assertSame(0, DB::table('notification_audit_jobs')->count());
        $this->assertNotNull($receipt->fresh()->push_completed_at);
        foreach (['created_at', 'updated_at', 'title', 'description', 'data', 'read_at'] as $field) {
            $this->assertSame($before[$field], $receipt->fresh()->getAttributes()[$field]);
        }
        $this->assertSame(1, Notification::count());
    }

    public static function localizedEvents(): array
    {
        $cases = [];
        $titles = [
            'upcoming_auction' => ['Auction starting soon', 'مزاد يبدأ قريباً'],
            'auction_started' => ['Auction started', 'بدأ المزاد'],
            'auction_ended' => ['Auction ended', 'انتهى المزاد'],
            'auction_status_changed' => ['Auction status updated', 'تحديث حالة المزاد'],
            'seller_request_status_changed' => ['Seller request updated', 'تحديث طلب البيع'],
            'order_status_changed' => ['Order status updated', 'تحديث حالة الطلب'],
            'order_payment_status_changed' => ['Order payment updated', 'تحديث دفع الطلب'],
        ];
        foreach ($titles as $event => $translations) {
            foreach (['en', 'ar'] as $index => $language) {
                $cases[$event.':'.$language] = [$event, $language, $translations[$index]];
            }
        }
        return $cases;
    }

    /** @dataProvider localizedEvents */
    public function test_actual_firebase_sdk_payload_matches_private_localized_history(string $event, string $language, string $title): void
    {
        app()->setLocale($language === 'ar' ? 'en' : 'ar');
        $user = $this->user('old-token', $language);
        if (strpos($event, 'auction') !== false) {
            $entity = LiveVideo::create(['user_id' => $user->id, 'status' => $event === 'auction_status_changed' ? 'start' : 'pending']);
            if ($event === 'upcoming_auction') {
                app(LifecycleNotificationService::class)->upcoming($entity);
            } else {
                $entity->update(['status' => ['auction_started' => 'start', 'auction_ended' => 'end', 'auction_status_changed' => 'pending'][$event]]);
            }
        } elseif ($event === 'seller_request_status_changed') {
            $this->sellerRequest($user)->update(['status' => 'approved']);
        } else {
            $entity = Order::create(['buyer_id' => $user->id, 'live_video_id' => LiveVideo::create([])->id,
                'status' => 'pending', 'payment_status' => 'unpaid']);
            $entity->update($event === 'order_status_changed' ? ['status' => 'shipping'] : ['payment_status' => 'paid']);
        }
        $receipt = Notification::first();
        $this->assertSame($title, $receipt->title);
        $this->assertSame($event, $receipt->type);
        $this->assertStringNotContainsString('notifications.', $receipt->description);
        $this->assertStringNotContainsString(':status', $receipt->description);
        if ($language === 'ar') { $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $receipt->description); }
        else { $this->assertDoesNotMatchRegularExpression('/[\x{0600}-\x{06FF}]/u', $receipt->description); }
        $user->update(['fcm_token' => 'current-token']);
        $messaging = \Mockery::mock(\Kreait\Firebase\Contract\Messaging::class);
        $messaging->shouldReceive('send')->once()->withArgs(function ($message) use ($receipt) {
            $payload = json_decode(json_encode($message, JSON_THROW_ON_ERROR), true);
            $this->assertSame('current-token', $payload['token']);
            $this->assertSame($receipt->title, $payload['notification']['title']);
            $this->assertSame($receipt->description, $payload['notification']['body']);
            $this->assertSame((string) $receipt->id, $payload['data']['notification_id']);
            $this->assertSame($receipt->type, $payload['data']['type']);
            foreach ($payload['data'] as $value) { $this->assertIsString($value); }
            $this->assertSame($receipt->data['navigation'], json_decode($payload['data']['navigation'], true));
            return true;
        })->andReturn([]);
        $service = (new \ReflectionClass(FCMService::class))->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty(FCMService::class, 'messaging');
        $property->setAccessible(true);
        $property->setValue($service, $messaging);
        $this->app->instance(FCMService::class, $service);
        (new SendStoredNotification($receipt->id))->handle();
        (new SendStoredNotification($receipt->id))->handle();
        $this->assertNotNull($receipt->fresh()->push_completed_at);
        $this->assertSame(1, Notification::count());
    }

    public function test_api_creation_event_and_read_timestamps_survive_push_and_replay(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-28 12:34:56', config('app.timezone')));
        $user = $this->user();
        $this->sellerRequest($user)->update(['status' => 'approved']);
        $receipt = Notification::first();
        $created = now()->toISOString();
        $this->assertSame($created, $receipt->created_at->toISOString());
        $this->assertSame($created, $receipt->data['occurred_at']);
        $this->travel(3)->minutes();
        $fcm = \Mockery::mock(FCMService::class);
        $fcm->shouldReceive('sendToDevice')->once()->andReturn(['success' => true]);
        $this->app->instance(FCMService::class, $fcm);
        (new SendStoredNotification($receipt->id))->handle();
        Passport::actingAs($user, [], 'api');
        $this->getJson('/api/user/notifications')->assertJsonPath('data.0.created_at', $created)
            ->assertJsonPath('data.0.data.occurred_at', $created)->assertJsonPath('data.0.title', 'Seller request updated');
        $this->postJson('/api/user/notifications/'.$receipt->id.'/mark-read')->assertJsonPath('success', true);
        $read = $receipt->fresh()->read_at->toISOString();
        $this->travel(1)->minutes();
        $this->postJson('/api/user/notifications/'.$receipt->id.'/mark-read');
        $this->assertSame($read, $receipt->fresh()->read_at->toISOString());
        $this->assertSame($created, $receipt->fresh()->created_at->toISOString());
    }

    public function test_scheduler_registers_existing_reminder_and_recovery_cadence(): void
    {
        $events = collect($this->app->make(Schedule::class)->events());
        foreach (['notifications:upcoming-auction-reminders' => '*/5 * * * *', 'notifications:retry-pushes' => '* * * * *'] as $command => $expression) {
            $matches = $events->filter(function ($event) use ($command) { return strpos($event->command, $command) !== false; });
            $this->assertCount(1, $matches);
            $this->assertSame($expression, $matches->first()->expression);
        }
    }

    protected function tearDown(): void
    {
        if ($this->hasJobsTable) { Schema::dropIfExists('notification_audit_jobs'); }
        parent::tearDown();
    }
}
