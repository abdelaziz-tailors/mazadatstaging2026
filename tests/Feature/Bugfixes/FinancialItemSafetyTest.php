<?php

namespace Tests\Feature\Bugfixes;

use App\Models\{LiveVideoItem, OrderItem, VideoComment, WalletTransaction};
use App\Services\{AuctionAwardService, AuctionWalletSettlement, OrderService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Spatie\Permission\Models\{Permission, Role};
use Tests\Support\AuctionBugfixTestCase;

class FinancialItemSafetyTest extends AuctionBugfixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::connection()->getPdo()->sqliteCreateFunction('regexp', fn ($pattern, $value) => preg_match('/'.$pattern.'/', $value ?? ''));
        Schema::table('users', function (Blueprint $t) { $t->decimal('wallet_balance', 12, 2)->default(0); });
        Schema::table('live_video_items', function (Blueprint $t) {
            $t->timestamp('end_at')->nullable(); $t->string('payment_status')->nullable(); $t->softDeletes();
        });
        Schema::table('live_videos', function (Blueprint $t) { $t->softDeletes(); });
        Schema::create('item_services', function (Blueprint $t) { $t->id(); });
        (require database_path('migrations/2026_06_26_120000_create_order_item_services.php'))->up();
        (require database_path('migrations/2026_06_22_100002_create_wallet_transactions_table.php'))->up();
        require_once database_path('migrations/New folder/2022_10_16_092804_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        // Other isolated suites create a smaller users table in this process.
        // Eloquent otherwise retains its old column list and discards wallet_balance.
        $columns = new \ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns');
        $columns->setAccessible(true); $columns->setValue(null, []);
    }

    private function payload($auction): array
    {
        return ['user_id' => $auction->user_id, 'title_ar' => 'Edited animal', 'bidding' => 10, 'quantity' => 1,
            'pieces' => [['age' => '2 years', 'gender' => 'female']]];
    }

    public static function financialStates(): array
    {
        return [['unawarded'], ['awarded'], ['ordered'], ['paid'], ['settled']];
    }

    /** @dataProvider financialStates */
    public function test_edit_preserves_historical_owner_and_award_order_and_settlement_remain_consistent(string $state): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $seller = $this->user(); $buyer = $this->user(['user_type' => 'buyer']);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'partner_id' => 90001]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'user_id' => $seller->id, 'quantity' => 1]);
        $order = null;
        if ($state !== 'unawarded') {
            $item->update(['user_finished_id' => $buyer->id, 'finished_price' => 100]);
        }
        if (in_array($state, ['ordered', 'paid', 'settled'], true)) {
            $order = OrderService::attachWonItem($item);
            if ($state !== 'ordered') { $order->update(['payment_status' => 'paid']); }
            if ($state === 'settled') { AuctionWalletSettlement::settleOrderIfNeeded($order->fresh()); }
        }
        $this->putJson(route('admin.products.update', $item->id), $this->payload($auction))->assertRedirect();
        $this->assertSame($seller->id, $item->fresh()->user_id);
        $this->assertNull($item->fresh()->seller_id);
        $this->assertSame('female', $item->fresh()->pieces->first()->gender);
        if ($state === 'unawarded') {
            VideoComment::create(['video_id' => $auction->id, 'live_video_item_id' => $item->id,
                'user_id' => $buyer->id, 'comment' => '100']);
        }
        app(AuctionAwardService::class)->award($item->id, null, $owner->id, true);
        $resolved = OrderService::attachWonItem($item);
        $this->assertSame($seller->id, $resolved->items->sole()->seller_id);
        if ($order) { $this->assertSame($order->id, $resolved->id); }
        $this->assertSame(1, OrderItem::count());
        $resolved->update(['payment_status' => 'paid']);
        AuctionWalletSettlement::settleOrderIfNeeded($resolved->fresh());
        AuctionWalletSettlement::settleOrderIfNeeded($resolved->fresh());
        $this->assertSame(100.0, (float) $seller->fresh()->wallet_balance);
        $this->assertSame(0.0, (float) $owner->fresh()->wallet_balance);
        $this->assertSame(0.0, (float) $buyer->fresh()->wallet_balance);
        $this->assertSame(1, WalletTransaction::where('type', 'seller_credit')->count());
        $this->assertSame($seller->id, WalletTransaction::where('type', 'seller_credit')->sole()->user_id);
    }

    public function test_committed_external_seller_cannot_be_changed_or_cleared_and_legacy_assignment_can_be_retained(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $original = $this->user(); $replacement = $this->user(); $this->submission($replacement, $owner, 'approved');
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'user_id' => $owner->id,
            'seller_id' => $original->id, 'finished_price' => 100, 'image' => '[]']);
        $view = app(\App\Http\Controllers\Dashboard\ProductController::class)->edit($item->id);
        $html = view('dashboard.pages.products._form', $view->getData() + ['errors' => new \Illuminate\Support\ViewErrorBag()])->render();
        $dom = new \DOMDocument(); @$dom->loadHTML($html);
        $this->assertSame(1, (new \DOMXPath($dom))->query('//select[@name="seller_id"]/option[@value="'.$original->id.'" and @selected]')->length);
        foreach ([['seller_source' => 'owner'], ['seller_source' => 'from_list', 'seller_id' => $replacement->id]] as $change) {
            $this->putJson(route('admin.products.update', $item->id), $change + $this->payload($auction))
                ->assertUnprocessable()->assertJsonValidationErrors('seller_id');
            $this->assertSame($original->id, $item->fresh()->seller_id);
        }
        $this->putJson(route('admin.products.update', $item->id), ['seller_source' => 'from_list', 'seller_id' => $original->id]
            + $this->payload($auction))->assertRedirect();
        $this->assertSame($original->id, $item->fresh()->seller_id);
    }

    public function test_deletion_uses_admin_ownership_even_when_legacy_partner_ids_collide(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $other = $this->user(['user_type' => 'vendor']); $foreignAdmin = $this->admin($other);
        $auction = $this->auction($other, ['admin_id' => $foreignAdmin->id, 'partner_id' => $admin->id]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        auth('admin')->setUser($admin);
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertForbidden();
        $this->assertNotNull($item->fresh());
        auth('admin')->setUser($foreignAdmin);
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertRedirect();
        $this->assertNull($item->fresh());
    }

    /** @dataProvider committedStates */
    public function test_committed_lot_cannot_be_deleted_including_an_archived_order(string $state): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $buyer = $this->user(); $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'user_id' => $owner->id,
            'user_finished_id' => $buyer->id, 'finished_price' => 100]);
        if ($state !== 'awarded') {
            $order = OrderService::attachWonItem($item);
            if (in_array($state, ['paid', 'settled'], true)) { $order->update(['payment_status' => 'paid']); }
            if ($state === 'settled') { AuctionWalletSettlement::settleOrderIfNeeded($order->fresh()); }
            if ($state === 'archived') {
                $order->delete();
                $item->update(['user_finished_id' => null, 'finished_price' => null]);
            }
        }
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertUnprocessable()->assertJsonValidationErrors('item');
        $this->assertNotNull($item->fresh());
    }

    public static function committedStates(): array { return [['awarded'], ['ordered'], ['paid'], ['settled'], ['archived']]; }

    public function test_legacy_item_paid_flag_alone_protects_seller_identity_and_deletion(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $replacement = $this->user(); $this->submission($replacement, $owner, 'approved');
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'user_id' => $owner->id, 'payment_status' => 'paid']);
        $this->putJson(route('admin.products.update', $item->id), $this->payload($auction)
            + ['seller_source' => 'from_list', 'seller_id' => $replacement->id])->assertUnprocessable()->assertJsonValidationErrors('seller_id');
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertUnprocessable()->assertJsonValidationErrors('item');
        $this->assertNotNull($item->fresh()); $this->assertNull($item->fresh()->seller_id);
    }

    public function test_admin_requires_permission_but_super_admin_and_authorized_admin_can_delete(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner, 'admin');
        $auction = $this->auction($owner); $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertForbidden();
        $permission = Permission::create(['name' => 'delete video', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertRedirect();
        $admin->revokePermissionTo($permission);
        $admin->assignRole(Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']));
        $item = LiveVideoItem::create(['live_video_id' => $auction->id]);
        $this->deleteJson(route('admin.products.destroy', $item->id))->assertRedirect();
    }

    public function test_scheduled_winner_receipt_is_private_idempotent_and_uses_generic_delivery(): void
    {
        DB::connection()->getPdo()->sqliteCreateFunction('TIMESTAMP', fn ($date, $time) => $date.' '.$time);
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $buyer = $this->user(['user_type' => 'buyer', 'fcm_token' => 'winner-device']);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'status' => 'start',
            'date_start_at' => now()->subDay()->toDateString(), 'time_start_at' => '10:00',
            'date_end_at' => now()->subDay()->toDateString(), 'time_end_at' => '11:00',
            'schedule_end_utc' => now('UTC')->subHour()->format('Y-m-d H:i:s')]);
        $item = LiveVideoItem::create(['live_video_id' => $auction->id, 'user_id' => $owner->id]);
        VideoComment::create(['video_id' => $auction->id, 'live_video_item_id' => $item->id, 'user_id' => $buyer->id, 'comment' => '100']);
        $this->artisan('livevideoEnd:check')->assertExitCode(0);
        $this->artisan('livevideoEnd:check')->assertExitCode(0);
        $receipt = \App\Models\Notification::where('type', 'auction_won')->sole();
        $this->assertSame($buyer->id, $receipt->user_id);
        $this->assertStringContainsString('Congratulations!', $receipt->description);
        $this->assertSame($item->id, $receipt->data['live_video_item_id']);
        \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\SendFCMNotification::class);
        $fcm = \Mockery::mock(\App\Services\FCMService::class);
        $fcm->shouldReceive('sendToDevice')->once()->with('winner-device', 'Mazadat',
            'Open the app to view your notifications.', ['type' => 'inbox_updated', 'screen' => 'notification',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'])->andReturn(['success' => true]);
        $this->app->instance(\App\Services\FCMService::class, $fcm);
        (new \App\Jobs\SendStoredNotification($receipt->id))->handle();
        (new \App\Jobs\SendStoredNotification($receipt->id))->handle();
        $this->assertSame(1, OrderItem::count());
    }
}
