<?php

namespace Tests\Feature\Bugfixes;

use App\Http\Requests\api\Video\AddVideoRequest;
use App\Models\LiveVideo;
use App\Models\UserSubscription;
use App\Services\{AgoraService, FCMService, SubscriptionService};
use Carbon\{Carbon, CarbonImmutable};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Route, Schema};
use Laravel\Passport\Passport;
use Tests\Support\AuctionBugfixTestCase;

class AuctionScheduleCompatibilityTest extends AuctionBugfixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-10 10:00:00', 'UTC'));
        config(['app.timezone' => 'UTC']);
        Schema::create('user_subscriptions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('status'); $t->timestamp('expires_at');
            $t->timestamp('replaced_at')->nullable(); $t->integer('remaining_auctions'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('video_views', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('video_id'); });
        Schema::create('video_favorites', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('video_id'); $t->unsignedBigInteger('user_id'); $t->softDeletes();
        });
        Schema::create('friends', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('friend_id'); $t->string('type')->nullable(); $t->softDeletes();
        });
        Schema::table('follow_users', function (Blueprint $t) { $t->softDeletes(); });
        $this->app->instance(FCMService::class, \Mockery::mock(FCMService::class)->shouldNotReceive('sendToMultipleDevices')->getMock());
        $this->app->instance(AgoraService::class, \Mockery::mock(AgoraService::class)->shouldIgnoreMissing());
        Route::post('/bugfix/mobile-create', function (AddVideoRequest $request) {
            return ['success' => true, 'schedule' => \App\Support\AuctionScheduleUtc::fromFields($request->all(),
                \App\Support\AuctionScheduleUtc::mobileTimezone($request->input('timezone')))];
        });
    }

    protected function tearDown(): void { CarbonImmutable::setTestNow(); parent::tearDown(); }

    private function payload(): array
    {
        return ['title_ar' => 'Future auction', 'information_ar' => 'Details', 'type' => 'live', 'video_type' => 'live',
            'date_start_at' => '2026-11-10', 'time_start_at' => '12:01', 'date_end_at' => '2026-11-11',
            'time_end_at' => '13:00', 'timezone' => 'Africa/Cairo', 'start_price' => 0,
            'commission_amount' => 5, 'commission_payer' => 'buyer'];
    }

    public function test_pending_dashboard_live_update_rejects_past_and_accepts_future_local_start(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $this->admin($owner);
        $service = \Mockery::mock(SubscriptionService::class);
        $service->shouldReceive('createAuction')->once()->andReturnUsing(fn ($fields) => LiveVideo::create($fields));
        $this->app->instance(SubscriptionService::class, $service);
        $this->postJson(route('admin.videos.store'), $this->payload())->assertRedirect();
        $auction = LiveVideo::sole();
        $before = $auction->getAttributes();
        foreach ([['time_start_at' => '11:59'], ['date_start_at' => '2026-11-09']] as $past) {
            $this->putJson(route('admin.videos.update', $auction->id), array_replace($this->payload(), $past))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(isset($past['date_start_at']) ? 'date_start_at' : 'time_start_at')
                ->assertJsonMissingValidationErrors(isset($past['date_start_at']) ? 'time_start_at' : 'date_start_at');
            $this->assertSame($before, $auction->fresh()->getAttributes());
        }
        $this->putJson(route('admin.videos.update', $auction->id), array_replace($this->payload(), ['time_start_at' => '12:02']))
            ->assertRedirect();
        $this->assertSame('2026-11-10 10:02:00', $auction->fresh()->schedule_start_utc);
    }

    public function test_recorded_past_start_and_non_pending_update_restrictions_remain(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $this->putJson(route('admin.videos.update', $auction->id), array_replace($this->payload(), ['type' => 'recorded', 'time_start_at' => '11:59']))
            ->assertUnprocessable()->assertJsonValidationErrors('time_start_at');
        foreach (['start', 'end'] as $status) {
            $auction->update(['status' => $status]); $before = $auction->fresh()->getAttributes();
            $this->putJson(route('admin.videos.update', $auction->id), $this->payload())->assertRedirect();
            $this->assertSame($before, $auction->fresh()->getAttributes());
        }
    }

    public static function zones(): array
    {
        return [[null, 'Africa/Cairo', '2026-11-10 10:01:00', '2026-11-10'],
            ['Africa/Cairo', 'Africa/Cairo', '2026-11-10 10:01:00', '2026-11-10'],
            ['UTC', 'UTC', '2026-11-10 12:01:00', '2026-11-10'],
            [null, 'Africa/Cairo', '2027-07-01 09:01:00', '2027-07-01'],
            ['Africa/Cairo', 'Africa/Cairo', '2027-07-01 09:01:00', '2027-07-01'],
            ['UTC', 'UTC', '2027-07-01 12:01:00', '2027-07-01']];
    }

    /** @dataProvider zones */
    public function test_mobile_validation_and_actual_update_persist_identical_legacy_and_explicit_instants(?string $zone, string $storedZone, string $expected, string $date): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner); Passport::actingAs($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'date_start_at' => '2026-11-10', 'time_start_at' => '12:01',
            'date_end_at' => '2026-11-11', 'time_end_at' => '13:00']);
        $payload = $this->payload(); unset($payload['timezone']);
        $payload['date_start_at'] = $date;
        $payload['date_end_at'] = CarbonImmutable::parse($date)->addDay()->format('Y-m-d');
        if ($zone !== null) { $payload['timezone'] = $zone; }
        UserSubscription::create(['user_id' => $owner->id, 'status' => 'approved', 'expires_at' => now()->addMonth(), 'remaining_auctions' => 2]);
        $service = \Mockery::mock(SubscriptionService::class);
        $service->shouldReceive('createAuction')->once()->andReturnUsing(function ($fields) use ($expected, $storedZone) {
            $this->assertSame($expected, $fields['schedule_start_utc']);
            $this->assertSame($storedZone, $fields['schedule_timezone']);
            return LiveVideo::create($fields);
        });
        $this->app->instance(SubscriptionService::class, $service);
        $this->postJson('/api/live/add', $payload)->assertOk()->assertJsonPath('success', true);
        $this->postJson('/bugfix/mobile-create', $payload)->assertJsonPath('success', true)
            ->assertJsonPath('schedule.schedule_start_utc', $expected)->assertJsonPath('schedule.schedule_timezone', $storedZone);
        $this->postJson('/api/live/update/'.$auction->id, $payload)->assertOk()->assertJsonPath('success', true);
        $this->assertSame($expected, $auction->fresh()->schedule_start_utc);
        $this->assertSame($storedZone, $auction->fresh()->schedule_timezone);
        $before = $auction->fresh()->getAttributes();
        $this->postJson('/api/live/update/'.$auction->id, ['date_start_at' => '2026-11-10', 'time_start_at' => '09:59'])
            ->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['time_start_at']]);
        $this->assertSame($before, $auction->fresh()->getAttributes());
        $this->postJson('/api/live/update/'.$auction->id, ['timezone' => 'invalid/timezone'])
            ->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['timezone']]);
    }

    public function test_mobile_partial_update_uses_stored_zone_and_timezone_only_update_recomputes_utc(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner); Passport::actingAs($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'schedule_timezone' => 'UTC',
            'date_start_at' => '2026-11-10', 'time_start_at' => '12:01:00', 'date_end_at' => '2026-11-11', 'time_end_at' => '13:00:00']);
        $this->postJson('/api/live/update/'.$auction->id, ['time_start_at' => '12:02'])->assertJsonPath('success', true);
        $this->assertSame('2026-11-10 12:02:00', $auction->fresh()->schedule_start_utc);
        $this->postJson('/api/live/update/'.$auction->id, ['timezone' => 'Africa/Cairo'])->assertJsonPath('success', true);
        $this->assertSame('2026-11-10 10:02:00', $auction->fresh()->schedule_start_utc);
    }

    public function test_nonexistent_dst_wall_time_is_a_validation_error_before_creation(): void
    {
        Passport::actingAs($this->user(['user_type' => 'vendor']));
        $this->postJson('/api/live/add', array_replace($this->payload(), ['timezone' => 'America/New_York',
            'date_start_at' => '2027-03-14', 'time_start_at' => '02:30', 'date_end_at' => '2027-03-15']))
            ->assertOk()->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['timezone']]);
        $this->assertSame(0, LiveVideo::count());
    }

    public function test_legacy_null_type_and_timezone_update_uses_cairo_without_rewriting_type(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner); Passport::actingAs($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id, 'type' => null,
            'date_start_at' => '2026-11-10', 'time_start_at' => '12:01:00', 'date_end_at' => '2026-11-11', 'time_end_at' => '13:00:00']);
        $this->postJson('/api/live/update/'.$auction->id, ['time_start_at' => '12:02'])->assertJsonPath('success', true);
        $this->assertSame('2026-11-10 10:02:00', $auction->fresh()->schedule_start_utc);
        $this->assertSame('Africa/Cairo', $auction->fresh()->schedule_timezone);
        $this->assertNull($auction->fresh()->type);
    }
}
