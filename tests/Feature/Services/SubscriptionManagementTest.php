<?php

namespace Tests\Feature\Services;

use App\Models\LiveVideo;
use App\Models\Package;
use App\Models\User\User;
use App\Models\UserSubscription;
use App\Models\Notification;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    private $service;
    private $user;
    private $package;
    private $databasePath;
    private $mariaDb = false;
    private $fixtureUsers = [];

    protected function setUp(): void
    {
        parent::setUp();
        \Laravel\Telescope\Telescope::stopRecording();
        // SQLite by default; MariaDB requires the explicit isolated migrated test server.
        $this->mariaDb = getenv('SUBSCRIPTION_MARIADB_TESTS') === '1';
        if ($this->mariaDb) {
            $this->assertSame('testing', app()->environment());
            $this->assertSame('mysql', config('database.default'));
            $this->assertSame('127.0.0.1', config('database.connections.mysql.host'));
            $this->assertSame('mazadat_subscription_test', config('database.connections.mysql.database'));
            $this->assertSame('33317', (string) config('database.connections.mysql.port'));
            $this->assertEmpty(config('database.connections.mysql.url'));
            $this->assertEmpty(config('database.connections.mysql.unix_socket'));
            config(['database.connections.subscription_test' => config('database.connections.mysql')]);
            config(['database.default' => 'subscription_test']);
            DB::purge('subscription_test');
            $engines = DB::select("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('users','user_subscriptions','packages','live_videos','notifications','live_video_items','live_video_item_pieces')");
            $this->assertCount(7, $engines);
            foreach ($engines as $table) { $this->assertSame('InnoDB', $table->ENGINE, $table->TABLE_NAME); }
        } else {
        $this->databasePath = tempnam(sys_get_temp_dir(), 'mazadat-subscription-');
        config(['database.default' => 'subscription_test', 'database.connections.subscription_test' => [
            'driver' => 'sqlite', 'database' => $this->databasePath, 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('subscription_test');
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('user_name'); $t->string('phone'); $t->string('email'); $t->string('gender');
            $t->string('user_type')->nullable(); $t->boolean('is_verified')->default(false); $t->timestamps(); $t->softDeletes();
            $t->string('app_lang')->default('en');
        });
        Schema::create('packages', function (Blueprint $t) {
            $t->id(); $t->text('name'); $t->text('description')->nullable(); $t->text('features')->nullable();
            $t->integer('auctions_limit'); $t->decimal('monthly_price'); $t->decimal('annual_price'); $t->boolean('is_active');
            $t->decimal('coin')->nullable(); $t->decimal('price')->nullable(); $t->string('image')->nullable(); $t->string('subscription_type')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('user_subscriptions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('package_id'); $t->string('subscription_type');
            $t->integer('auctions_limit'); $t->integer('remaining_auctions'); $t->decimal('price'); $t->string('image')->nullable();
            $t->string('status')->nullable(); $t->text('rejection_reason')->nullable(); $t->timestamp('expires_at')->nullable();
            $t->timestamp('expiry_reminder_sent_at')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        require_once database_path('migrations/2026_10_26_000001_add_subscription_request_snapshots.php');
        (new \AddSubscriptionRequestSnapshots())->up();
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('type'); $t->string('title'); $t->text('description'); $t->json('data'); $t->timestamps();
            $t->timestamp('read_at')->nullable();
        });
        Schema::create('live_videos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('title'); $t->timestamps();
        });
        }
        $this->service = app(SubscriptionService::class);
        $this->user = $this->createUser('Subscriber');
        $this->package = Package::create(['name' => '{"en":"Gold","ar":"Gold"}', 'auctions_limit' => 2,
            'monthly_price' => 20, 'annual_price' => 200, 'is_active' => 1]);
        Carbon::setTestNow('2026-01-31 12:34:56');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        \Carbon\CarbonImmutable::setTestNow();
        if ($this->mariaDb && $this->fixtureUsers) {
            $packages = UserSubscription::whereIn('user_id', $this->fixtureUsers)->pluck('package_id')->all();
            if ($this->package) { $packages[] = $this->package->id; }
            DB::table('users')->whereIn('id', $this->fixtureUsers)->delete();
            DB::table('packages')->whereIn('id', array_unique($packages))->delete();
        }
        DB::disconnect('subscription_test');
        if ($this->databasePath && is_file($this->databasePath)) {
            unlink($this->databasePath);
        }
        parent::tearDown();
    }

    private function createUser(string $name): User
    {
        $suffix = bin2hex(random_bytes(8));
        $user = User::create(['name' => $name, 'user_name' => 'subscription_'.$suffix,
            'phone' => $suffix, 'email' => $suffix.'@example.test', 'gender' => 'male']);
        $this->fixtureUsers[] = $user->id;
        return $user;
    }

    private function request(string $cycle = 'monthly'): UserSubscription
    {
        return $this->service->request($this->user->id, $this->package, $cycle);
    }

    public function test_snapshot_approval_replacement_and_retries_preserve_history()
    {
        $old = $this->request();
        $this->service->decide($old->id, true);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'First']);
        $next = $this->request('annual');
        $this->assertSame($old->id, UserSubscription::getActiveSubscription($this->user->id)->id);
        $this->package->update(['auctions_limit' => 99, 'annual_price' => 999, 'name' => '{"en":"Changed"}']);
        Carbon::setTestNow('2026-02-03 10:00:00');
        $this->assertTrue($this->service->decide($next->id, true));
        $next->refresh();
        $this->assertEquals(2, $next->remaining_auctions);
        $this->assertEquals(200, $next->price);
        $this->assertSame('{"en":"Gold","ar":"Gold"}', $next->package_snapshot['name']);
        $this->assertSame('2027-02-03 10:00:00', $next->expires_at->toDateTimeString());
        $this->assertNotNull($old->fresh()->replaced_at);
        $this->assertEquals(1, $old->fresh()->remaining_auctions);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Second']);
        $this->assertFalse($this->service->decide($next->id, true));
        $this->assertFalse($this->service->decide($old->id, true));
        $this->assertFalse($this->service->decide($next->id, false));
        $this->assertEquals(1, $next->fresh()->remaining_auctions);
        $this->assertSame(2, Notification::count());
    }

    public function test_one_pending_request_and_decline_leaves_current_unchanged()
    {
        $old = $this->request(); $this->service->decide($old->id, true);
        $pending = $this->request();
        try { $this->request('annual'); $this->fail('Duplicate pending request accepted'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('package_id', $e->errors()); }
        $before = $old->fresh()->getAttributes();
        $this->assertTrue($this->service->decide($pending->id, false, 'Declined'));
        $this->assertFalse($this->service->decide($pending->id, false));
        $this->assertFalse($this->service->decide($pending->id, true));
        $this->assertSame($before, $old->fresh()->getAttributes());
        $this->assertSame('pending', $this->request()->status);
        $this->assertSame(2, Notification::count());
    }

    public function test_quota_is_atomic_failed_insert_and_transaction_rollback_consume_zero_deletion_restores_nothing()
    {
        $subscription = $this->request(); $this->service->decide($subscription->id, true);
        try { $this->service->saveAuction((new LiveVideo())->forceFill(['user_id' => $this->user->id, 'invalid_subscription_test_column' => 1])); $this->fail('Invalid insert accepted'); }
        catch (\Illuminate\Database\QueryException $e) { $this->assertEquals(2, $subscription->fresh()->remaining_auctions); }
        try {
            DB::transaction(function () {
                $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Rolled back transfer']);
                throw new \RuntimeException('Transfer failed');
            });
        } catch (\RuntimeException $e) { $this->assertEquals(2, $subscription->fresh()->remaining_auctions); }
        $auction = $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Success']);
        $auction->update(['title' => 'Updated']); $auction->delete();
        $this->assertEquals(1, $subscription->fresh()->remaining_auctions);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Last']);
        $this->assertEquals(2, $subscription->fresh()->used_auctions);
        $this->expectException(ValidationException::class);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Exhausted']);
    }

    public function test_expired_subscription_blocks_creation_but_allows_a_new_request()
    {
        $subscription = $this->request(); $this->service->decide($subscription->id, true);
        $this->assertSame('2026-02-28 12:34:56', $subscription->fresh()->expires_at->toDateTimeString());
        Carbon::setTestNow($subscription->fresh()->expires_at);
        $this->assertSame('pending', $this->request()->status);
        $this->expectException(ValidationException::class);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Expired']);
    }

    public function test_exhausted_subscription_allows_switching_and_approval_starts_full_quota()
    {
        $current = $this->request(); $this->service->decide($current->id, true);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'One']);
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Two']);
        $other = $this->package->replicate(); $other->auctions_limit = 5; $other->save();
        $pending = $this->service->request($this->user->id, $other, 'annual');
        $this->assertNull(UserSubscription::getActiveSubscription($this->user->id));
        $this->service->decide($pending->id, true);
        $this->assertEquals(5, $pending->fresh()->remaining_auctions);
        $this->assertSame(0, $pending->fresh()->used_auctions);
    }

    public function test_auction_transaction_retry_inserts_once_and_consumes_once()
    {
        $pending = $this->request(); $this->service->decide($pending->id, true);
        $failOnce = true;
        DB::listen(function ($query) use (&$failOnce) {
            if ($failOnce && preg_match('/insert into ["`]live_videos["`]/', $query->sql)) {
                $failOnce = false;
                throw new \PDOException('database is locked');
            }
        });
        $auction = $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Retried']);
        $this->assertFalse($failOnce);
        $this->assertTrue($auction->exists);
        $this->assertSame(1, LiveVideo::count());
        $this->assertEquals(1, $pending->fresh()->remaining_auctions);
    }

    public function test_dashboard_packages_current_highlight_pending_restriction_and_history_render()
    {
        $current = $this->request(); $this->service->decide($current->id, true);
        $pending = $this->request();
        $template = tempnam(sys_get_temp_dir(), 'subscription-view-');
        $templateWithExtension = $template.'.blade.php';
        try {
            $source = file_get_contents(resource_path('views/dashboard/pages/user-subscriptions/index.blade.php'));
            $source = str_replace("@extends('dashboard.layouts.app')", '', $source);
            file_put_contents($templateWithExtension, $source."\n@yield('content')");
            $html = view()->file($templateWithExtension, ['plans' => Package::all(), 'current' => $current->fresh(),
                'history' => collect([$pending, $current])])->render();
            $this->assertStringContainsString('border border-success', $html);
            $this->assertStringContainsString('disabled', $html);
            $this->assertStringContainsString('name="subscription_type"', $html);
            $this->assertStringContainsString('Gold', $html);
        } finally {
            @unlink($template);
            @unlink($templateWithExtension);
            @unlink(app('blade.compiler')->getCompiledPath($templateWithExtension));
        }
    }

    public function test_history_http_endpoint_requires_authentication()
    {
        $this->withoutMiddleware(\App\Http\Middleware\APISettings::class);
        $response = $this->getJson('/api/user/subscription-history');
        $response->assertStatus(401);
    }

    public function test_dashboard_and_mobile_share_pending_restrictions_and_renewal_flow()
    {
        \Laravel\Passport\Passport::actingAs($this->user, [], 'api');
        $admin = new \App\Models\Admin(['type' => 'partner', 'user_id' => $this->user->id]);
        auth('admin')->setUser($admin);
        $dashboard = app(\App\Http\Controllers\Dashboard\UserSubscriptionController::class);
        $mobile = app(\App\Http\Controllers\api\User\Profile\AuctionSubscriptionController::class);
        $input = ['package_id' => $this->package->id, 'subscription_type' => 'monthly'];
        $dashboard->store(\Illuminate\Http\Request::create('/', 'POST', $input));
        $pending = UserSubscription::firstOrFail();
        try {
            $mobile->subscribe(\Illuminate\Http\Request::create('/', 'POST', $input));
            $this->fail('Mobile accepted a second Pending request');
        } catch (ValidationException $e) { $this->assertArrayHasKey('package_id', $e->errors()); }
        $this->service->decide($pending->id, true);
        $response = $mobile->renew(\Illuminate\Http\Request::create('/', 'POST'));
        $this->assertTrue($response->getData()->success);
        $this->assertSame(2, UserSubscription::count());
        $this->assertSame($pending->id, UserSubscription::getActiveSubscription($this->user->id)->id);
        try {
            $dashboard->store(\Illuminate\Http\Request::create('/', 'POST', $input));
            $this->fail('Dashboard accepted a second Pending request');
        } catch (ValidationException $e) { $this->assertArrayHasKey('package_id', $e->errors()); }
        $this->assertArrayHasKey('plans', $dashboard->index()->getData());
    }

    public function test_partner_cannot_approve_requests()
    {
        auth('admin')->setUser(new \App\Models\Admin(['type' => 'partner', 'user_id' => $this->user->id]));
        $pending = $this->request();
        try {
            app(\App\Http\Controllers\Dashboard\UserSubscriptionController::class)->approve($pending->id);
            $this->fail('Partner approved a request');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame(0, Notification::count());
    }

    /** @dataProvider subscriptionNotificationLanguages */
    public function test_decision_notification_uses_subscriber_language_in_storage_and_mobile_api(
        string $subscriberLocale, string $adminLocale, bool $approve, string $expected
    ) {
        $this->user->update(['app_lang' => $subscriberLocale]);
        $pending = $this->request();
        app()->setLocale($adminLocale);

        $this->assertTrue($this->service->decide($pending->id, $approve));
        $this->assertSame($adminLocale, app()->getLocale());
        $status = $approve ? 'approved' : 'rejected';
        $notification = Notification::sole();
        $this->assertSame($this->user->id, $notification->user_id);
        $this->assertSame('subscription', $notification->type);
        $this->assertSame($expected, $notification->title);
        $this->assertSame($expected, $notification->description);
        $this->assertSame(['user_subscription_id' => $pending->id, 'status' => $status], $notification->data);
        $this->assertSame($status, $pending->fresh()->status);
        $this->assertFalse($this->service->decide($pending->id, $approve));
        $this->assertSame(1, Notification::count());

        \Laravel\Passport\Passport::actingAs($this->user->fresh(), [], 'api');
        // Exercise the real route/controller/resource; the shared API key is unrelated to localization.
        $this->withoutMiddleware('APISettings')->getJson('/api/user/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.type', 'subscription')
            ->assertJsonPath('data.0.title', $expected)
            ->assertJsonPath('data.0.description', $expected)
            ->assertJsonPath('data.0.data.user_subscription_id', $pending->id)
            ->assertJsonPath('data.0.data.status', $status)
            ->assertJsonPath('data.0.is_read', false)
            ->assertJsonPath('unread_count', 1);
    }

    public static function subscriptionNotificationLanguages(): array
    {
        $cases = [];
        foreach (['ar', 'en'] as $subscriberLocale) {
            foreach (['ar', 'en'] as $adminLocale) {
                foreach ([true, false] as $approve) {
                    $expected = $subscriberLocale === 'ar'
                        ? ($approve ? 'تمت الموافقة على الاشتراك بنجاح' : 'تم رفض الاشتراك بنجاح')
                        : ($approve ? 'Subscription Approved Successfully' : 'Subscription Rejected Successfully');
                    $cases[$subscriberLocale.' subscriber, '.$adminLocale.' admin, '.($approve ? 'approve' : 'reject')]
                        = [$subscriberLocale, $adminLocale, $approve, $expected];
                }
            }
        }
        return $cases;
    }

    public function test_notification_failure_rolls_back_the_entire_decision()
    {
        $current = $this->request(); $this->service->decide($current->id, true);
        $pending = $this->request();
        Notification::creating(function () { throw new \RuntimeException('Notification failure probe'); });
        try {
            $this->service->decide($pending->id, true);
            $this->fail('Approval committed without its notification');
        } catch (\RuntimeException $e) {
            $this->assertSame('Notification failure probe', $e->getMessage());
            $this->assertSame('pending', $pending->fresh()->status);
            $this->assertNull($current->fresh()->replaced_at);
        }
    }

    public function test_replaced_subscription_does_not_send_an_expiry_reminder()
    {
        $old = $this->request(); $this->service->decide($old->id, true);
        $old->update(['expires_at' => now()->addHours(12)]);
        $next = $this->request(); $this->service->decide($next->id, true);
        $this->artisan('notifications:subscription-expiry-reminders')->assertExitCode(0);
        $this->assertSame(0, Notification::where('type', 'payment_reminder')->count());
        $this->assertNull($old->fresh()->expiry_reminder_sent_at);
    }

    public function test_legacy_duplicate_pending_requests_require_explicit_admin_resolution()
    {
        $pending = $this->request();
        $duplicate = $pending->replicate(); $duplicate->save();
        $before = UserSubscription::orderBy('id')->get()->map->getAttributes()->all();
        try { $this->request(); $this->fail('Duplicate legacy Pending requests were ignored'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('package_id', $e->errors()); }
        $this->assertSame($before, UserSubscription::orderBy('id')->get()->map->getAttributes()->all());
        try { $this->service->decide($pending->id, true); $this->fail('Ambiguous legacy duplicates were approved'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('subscription', $e->errors()); }
        $this->assertSame($before, UserSubscription::orderBy('id')->get()->map->getAttributes()->all());
        $this->assertSame(0, Notification::count());
        $this->service->decide($pending->id, false, 'Resolve duplicate explicitly');
        try { $this->request(); $this->fail('Remaining Pending request was ignored'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('package_id', $e->errors()); }
        $this->service->decide($duplicate->id, false, 'Resolve duplicate explicitly');
        $this->assertSame('pending', $this->request()->status);
        $this->assertSame(3, UserSubscription::count());
    }

    public function test_legacy_approval_uses_saved_cycle_and_quota_and_refuses_missing_entitlement()
    {
        $legacy = $this->request('annual');
        $legacy->update(['package_snapshot' => null, 'duration_months' => null]);
        $this->package->update(['auctions_limit' => 99, 'annual_price' => 999]);
        $this->service->decide($legacy->id, true);
        $this->assertEquals(2, $legacy->fresh()->remaining_auctions);
        $this->assertEquals(200, $legacy->fresh()->price);
        $this->assertSame('2027-01-31 12:34:56', $legacy->fresh()->expires_at->toDateTimeString());
        $invalid = $this->request(); $invalid->update(['auctions_limit' => 0]);
        try { $this->service->decide($invalid->id, true); $this->fail('Missing legacy quota was approved'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('subscription', $e->errors()); }
        $this->assertSame('pending', $invalid->fresh()->status);
        $this->assertNull($legacy->fresh()->replaced_at);
        $this->assertSame(1, Notification::count());
    }

    public function test_schema_rollback_refuses_to_discard_populated_snapshots()
    {
        $pending = $this->request();
        require_once database_path('migrations/2026_10_26_000001_add_subscription_request_snapshots.php');
        try { (new \AddSubscriptionRequestSnapshots())->down(); $this->fail('Populated snapshot schema was dropped'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('Snapshot/lifecycle data exists', $e->getMessage()); }
        $this->assertNotNull($pending->fresh()->package_snapshot);
    }

    public function test_migrated_schema_roundtrip_preserves_legacy_data_and_foreign_key_index()
    {
        if (!$this->mariaDb) { $this->markTestSkipped('Requires real MariaDB DDL and foreign-key indexes.'); }
        require_once database_path('migrations/2026_10_26_000001_add_subscription_request_snapshots.php');
        $migration = new \AddSubscriptionRequestSnapshots();
        $migration->down();
        $legacy = UserSubscription::create(['user_id' => $this->user->id, 'package_id' => $this->package->id,
            'subscription_type' => 'annual', 'auctions_limit' => 20, 'remaining_auctions' => 7,
            'price' => 200, 'status' => 'approved', 'expires_at' => now()->addYear()]);
        $before = $legacy->getAttributes();
        $migration->up();
        $legacy->refresh();
        foreach ($before as $column => $value) { $this->assertEquals($value, $legacy->getRawOriginal($column), $column); }
        $this->assertNull($legacy->package_snapshot);
        $this->assertNull($legacy->started_at);
        $this->assertNull($legacy->replaced_at);
        $this->assertTrue(collect(DB::select('SHOW INDEX FROM user_subscriptions'))->contains(function ($index) {
            return $index->Column_name === 'user_id' && (int) $index->Seq_in_index === 1;
        }));
    }

    public function test_nullable_legacy_pending_status_uses_the_same_request_and_decision_gate()
    {
        if ($this->mariaDb) { $this->markTestSkipped('Stock MariaDB schema disallows NULL status; custom legacy schema is covered by SQLite.'); }
        $pending = $this->request(); $pending->update(['status' => null]);
        $this->assertTrue($pending->fresh()->isPending());
        try { $this->request(); $this->fail('NULL legacy Pending request was ignored'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('package_id', $e->errors()); }
        $this->assertTrue($this->service->decide($pending->id, false));
        $this->assertSame('pending', $this->request()->status);
    }

    public function test_migrated_transfer_failure_rolls_back_quota_and_existing_target_transfer_spends_zero()
    {
        if (!$this->mariaDb) { $this->markTestSkipped('Requires the full migrated MariaDB transfer schema.'); }
        $pending = $this->request(); $this->service->decide($pending->id, true);
        $source = LiveVideo::create(['user_id' => $this->user->id, 'title' => 'Source', 'status' => 'end', 'type' => 'recorded']);
        $item = \App\Models\LiveVideoItem::create(['live_video_id' => $source->id, 'quantity' => 1]);
        $service = app(\App\Services\AuctionItemTransferService::class);
        try { $service->createAuctionAndTransferItems($source, [], [PHP_INT_MAX]); $this->fail('Invalid transfer succeeded'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('item_ids', $e->errors()); }
        $this->assertSame(1, LiveVideo::count());
        $this->assertEquals(2, $pending->fresh()->remaining_auctions);
        [$target, $items] = $service->createAuctionAndTransferItems($source, [], [$item->id]);
        $this->assertEquals($target->id, $items->first()->live_video_id);
        $this->assertEquals(1, $pending->fresh()->remaining_auctions);
        $another = \App\Models\LiveVideoItem::create(['live_video_id' => $source->id, 'quantity' => 1]);
        $service->transferItems($source, $target, [$another->id]);
        $target->delete();
        $this->assertEquals(1, $pending->fresh()->remaining_auctions);
    }

    public function test_migrated_dashboard_and_mobile_creation_share_the_same_quota()
    {
        if (!$this->mariaDb) { $this->markTestSkipped('Requires the migrated MariaDB HTTP schema.'); }
        \Carbon\CarbonImmutable::setTestNow('2026-01-31 12:34:56');
        \Mockery::mock('overload:App\Http\Controllers\FirebaseController')->shouldIgnoreMissing();
        \Mockery::mock('overload:App\Services\FCMService')->shouldIgnoreMissing();
        \Illuminate\Support\Facades\Queue::fake();
        $this->withoutMiddleware(['APISettings', \App\Http\Middleware\APISettings::class]);
        $this->withoutMiddleware(\App\Http\Middleware\SetDashboardLocale::class);
        $admin = (new \App\Models\Admin(['type' => 'partner', 'user_id' => $this->user->id]))->forceFill(['id' => 1]);
        auth('admin')->setUser($admin);
        $this->user->setRelation('admin', $admin);
        \Laravel\Passport\Passport::actingAs($this->user, [], 'api');
        $pending = $this->request(); $this->service->decide($pending->id, true);
        $input = ['title' => 'Quota integration', 'title_ar' => 'Quota integration', 'information_ar' => 'Integration information', 'type' => 'photo', 'video_type' => 'photo',
            'date_start_at' => '2026-02-01', 'date_end_at' => '2026-02-01', 'time_start_at' => '12:00', 'time_end_at' => '13:00',
            'start_price' => 10, 'commission_amount' => 0, 'commission_payer' => 'buyer'];
        $this->post(route('admin.videos.store'), $input)->assertStatus(302);
        $this->assertEquals(1, $pending->fresh()->remaining_auctions);
        $this->postJson('/api/live/add', $input)->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(0, $pending->fresh()->remaining_auctions);
        $this->assertSame(2, LiveVideo::where('user_id', $this->user->id)->count());
        $this->postJson('/api/live/add', $input)->assertStatus(422)->assertJson(['success' => false]);
        $this->postJson(route('admin.videos.store'), $input)->assertStatus(422);
        $this->assertSame(2, LiveVideo::where('user_id', $this->user->id)->count());
    }
    /** Two independent PHP processes wait at a barrier, then enter the same operation. */
    private function race(string $operation, ?int $id = null, ?string $secondOperation = null): array
    {
        $configuration = base64_encode(json_encode(config('database.connections.subscription_test')));
        $bootstrap = base64_encode(base_path());
        $barrier = null;
        if ($this->mariaDb) {
            config(['database.connections.subscription_barrier' => config('database.connections.subscription_test')]);
            $barrier = DB::connection('subscription_barrier');
            $barrier->beginTransaction();
            $barrier->table('users')->where('id', $this->user->id)->lockForUpdate()->first();
        }
        $code = <<<'PHP'
error_reporting(8191);
chdir(base64_decode('%s'));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Laravel\Telescope\Telescope::stopRecording();
config(['database.default' => 'subscription_test', 'database.connections.subscription_test' => json_decode(base64_decode('%s'), true)]);
Illuminate\Support\Facades\DB::purge('subscription_test');
$mysql = Illuminate\Support\Facades\DB::getDriverName() === 'mysql';
if (!$mysql) { Illuminate\Support\Facades\DB::statement('PRAGMA busy_timeout = 10000'); }
Carbon\Carbon::setTestNow('2026-01-31 12:34:56');
$connectionId = $mysql ? Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id : 0;
fwrite(STDOUT, "ready ".$connectionId."\n");
fgets(STDIN);
try {
    $service = app(App\Services\SubscriptionService::class);
    switch ('%s') {
        case 'request': $service->request(%d, App\Models\Package::findOrFail(%d), 'monthly'); $result = true; break;
        case 'approve': $result = $service->decide(%d, true); break;
        case 'decline': $result = $service->decide(%d, false, 'Concurrent decline'); break;
        case 'auction': $service->createAuction(['user_id' => %d, 'title' => 'Concurrent']); $result = true; break;
    }
    echo json_encode(['success' => $result]);
} catch (Illuminate\Validation\ValidationException $e) {
    echo json_encode(['validation' => true]);
} catch (Throwable $e) {
    echo json_encode(['error' => get_class($e).': '.$e->getMessage()]);
}
PHP;
        $workers = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $workerCode = sprintf($code, $bootstrap, $configuration, $i === 1 ? ($secondOperation ?? $operation) : $operation,
                    $this->user->id, $this->package->id, $id ?? 0, $id ?? 0, $this->user->id);
                $process = proc_open([PHP_BINARY, '-d', 'error_reporting=8191', '-r', $workerCode],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', 'NUL', 'a']], $pipes, base_path());
                $this->assertIsResource($process);
                stream_set_timeout($pipes[1], 20);
                $workers[] = [$process, $pipes];
            }
            $connectionIds = [];
            foreach ($workers as [, $pipes]) {
                $ready = trim(fgets($pipes[1]));
                $this->assertStringStartsWith('ready ', $ready);
                $connectionIds[] = (int) substr($ready, 6);
            }
            foreach ($workers as [, $pipes]) { fwrite($pipes[0], "go\n"); fclose($pipes[0]); }
            if ($barrier) {
                $deadline = microtime(true) + 15;
                do {
                    $status = (array) $barrier->selectOne('SHOW ENGINE INNODB STATUS');
                    $waiters = 0;
                    foreach ($connectionIds as $connectionId) {
                        $waiters += preg_match('/LOCK WAIT[^\n]*\n(?:[^\n]*\n){0,4}(?:MariaDB|MySQL) thread id '.$connectionId.'\b/', $status['Status']);
                    }
                    if ($waiters === 2) { break; }
                    usleep(50000);
                } while (microtime(true) < $deadline);
                $this->assertSame(2, $waiters, 'Both independent workers must overlap at the held subscriber-row lock. '.$status['Status']);
                $barrier->rollBack();
            }
            $results = [];
            foreach ($workers as [$process, $pipes]) {
                $output = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $this->assertSame(0, proc_close($process), $output);
                $results[] = json_decode($output, true);
                $this->assertIsArray(end($results), $output);
                $this->assertArrayNotHasKey('error', end($results), $output);
            }
            return $results;
        } finally {
            if ($barrier && $barrier->transactionLevel()) { $barrier->rollBack(); }
            foreach ($workers as [$process, $pipes]) {
                foreach ($pipes as $pipe) { if (is_resource($pipe)) { fclose($pipe); } }
                if (is_resource($process)) { proc_terminate($process); proc_close($process); }
            }
        }
    }

    public function test_simultaneous_requests_create_only_one_pending_request()
    {
        $results = collect($this->race('request'));
        $this->assertSame(1, $results->where('success', true)->count());
        $this->assertSame(1, $results->where('validation', true)->count());
        $this->assertSame(1, UserSubscription::where('status', 'pending')->count());
    }

    public function test_simultaneous_approvals_send_one_notification_and_do_not_reset_quota()
    {
        $pending = $this->request();
        $results = collect($this->race('approve', $pending->id));
        $this->assertSame(1, $results->where('success', true)->count());
        $this->assertSame(1, Notification::count());
        $this->service->createAuction(['user_id' => $this->user->id, 'title' => 'Consume']);
        $this->assertFalse($this->service->decide($pending->id, true));
        $this->assertEquals(1, $pending->fresh()->remaining_auctions);
    }

    public function test_concurrent_auction_creation_cannot_spend_the_last_unit_twice()
    {
        $this->package->update(['auctions_limit' => 1]);
        $pending = $this->request(); $this->service->decide($pending->id, true);
        $results = collect($this->race('auction'));
        $this->assertSame(1, $results->where('success', true)->count());
        $this->assertSame(1, $results->where('validation', true)->count());
        $this->assertSame(1, LiveVideo::count());
        $this->assertEquals(0, $pending->fresh()->remaining_auctions);
    }

    public function test_concurrent_approval_and_decline_have_one_terminal_decision_and_notification()
    {
        $current = $this->request(); $this->service->decide($current->id, true);
        $pending = $this->request();
        $results = collect($this->race('approve', $pending->id, 'decline'));
        $this->assertSame(1, $results->where('success', true)->count());
        $this->assertSame(2, Notification::count());
        $this->assertContains($pending->fresh()->status, ['approved', 'rejected']);
        $this->assertEquals($pending->fresh()->status === 'approved' ? $pending->id : $current->id,
            UserSubscription::currentSubscription($this->user->id)->id);
    }

    public function test_status_and_history_keep_current_separate_from_pending_and_scope_to_authenticated_user()
    {
        $current = $this->request(); $this->service->decide($current->id, true);
        $pending = $this->request('annual');
        $other = $this->createUser('Other');
        $this->service->request($other->id, $this->package, 'monthly');
        \Laravel\Passport\Passport::actingAs($this->user, [], 'api');
        $controller = app(\App\Http\Controllers\api\User\Profile\AuctionSubscriptionController::class);
        $status = $controller->getStatus()->getData(true)['data'];
        $this->assertEquals($current->id, $status['subscription']['id']);
        $this->assertEquals($pending->id, $status['pending_request']['id']);
        $this->assertSame(0, $status['subscription']['used_auctions']);
        $history = $controller->getHistory()->getData(true)['data'];
        $this->assertCount(2, $history);
        $this->assertEquals([$current->id, $pending->id], collect($history)->pluck('id')->sort()->values()->all());
        $plans = $controller->getPlans()->getData(true)['data'];
        $this->assertTrue($plans[0]['is_current']);
        $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/api/user/subscription-history', 'GET'));
        $this->assertContains('auth:api', $route->gatherMiddleware());
    }
}
