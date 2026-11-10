<?php

namespace Tests\Feature\Bugfixes;

use App\Http\Requests\api\Video\AddVideoRequest;
use App\Models\LiveVideo;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Tests\Support\AuctionBugfixTestCase;

class AuctionInputRegressionTest extends AuctionBugfixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-10 10:00:00', 'UTC'));
        Route::post('/bugfix/auction-validation', function (AddVideoRequest $request) { return ['success' => true]; });
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function payload(): array
    {
        return ['title_ar' => 'Auction', 'information_ar' => 'Details', 'type' => 'live', 'video_type' => 'live',
            'date_start_at' => '2026-11-10', 'time_start_at' => '12:01', 'date_end_at' => '2026-11-11',
            'time_end_at' => '13:00', 'timezone' => 'Africa/Cairo', 'start_price' => 0,
            'commission_amount' => 5, 'commission_payer' => 'buyer'];
    }

    public function test_dashboard_and_mobile_reject_missing_or_invalid_payer_before_creation(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $this->admin($owner); Passport::actingAs($owner);
        $service = \Mockery::mock(SubscriptionService::class);
        $service->shouldNotReceive('createAuction'); $this->app->instance(SubscriptionService::class, $service);
        foreach ([null, '', 'Seller', 'invalid', []] as $payer) {
            $data = array_replace($this->payload(), ['commission_payer' => $payer]);
            $this->postJson(route('admin.videos.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('commission_payer');
            $this->postJson('/api/live/add', $data)->assertOk()->assertJsonPath('success', false)
                ->assertJsonStructure(['errors' => ['commission_payer']]);
        }
        $data = $this->payload(); unset($data['commission_payer']);
        $this->postJson(route('admin.videos.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('commission_payer');
        $this->postJson('/api/live/add', $data)->assertOk()->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['commission_payer']]);
        $this->assertSame(0, LiveVideo::count());
    }

    /** @dataProvider payers */
    public function test_valid_payer_and_future_live_schedule_reach_creation(string $payer): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $this->admin($owner); Passport::actingAs($owner);
        $service = \Mockery::mock(SubscriptionService::class);
        $service->shouldReceive('createAuction')->once()->andReturnUsing(function ($attributes) use ($payer) {
            $this->assertSame($payer, $attributes['commission_payer']);
            $this->assertSame('2026-11-10 10:01:00', $attributes['schedule_start_utc']);
            return LiveVideo::create($attributes);
        });
        $this->app->instance(SubscriptionService::class, $service);
        $data = array_replace($this->payload(), ['commission_payer' => $payer]);
        $this->postJson('/bugfix/auction-validation', $data)->assertOk()->assertJsonPath('success', true);
        $this->postJson(route('admin.videos.store'), $data)->assertRedirect();
        $this->assertSame(1, LiveVideo::count());
    }

    public static function payers(): array { return [['buyer'], ['seller']]; }

    public function test_past_live_dates_and_times_are_rejected_in_the_submitted_timezone(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $this->admin($owner); Passport::actingAs($owner);
        foreach ([['date_start_at' => '2026-11-09'], ['time_start_at' => '11:59'],
            ['timezone' => 'America/Los_Angeles', 'time_start_at' => '01:59']] as $invalid) {
            $data = array_replace($this->payload(), $invalid);
            $this->postJson(route('admin.videos.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('time_start_at');
            $this->postJson('/api/live/add', $data)->assertOk()->assertJsonPath('success', false)
                ->assertJsonStructure(['errors' => ['time_start_at']]);
        }
        $this->assertSame(0, LiveVideo::count());
    }

    public function test_legacy_mobile_keeps_cairo_timezone_and_invalid_zone_is_validation_error(): void
    {
        Passport::actingAs($this->user(['user_type' => 'vendor']));
        config(['app.timezone' => 'Asia/Kuwait']);
        $data = $this->payload(); unset($data['timezone']);
        $data['time_start_at'] = '11:59';
        $this->postJson('/bugfix/auction-validation', $data)->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['time_start_at']]);
        $data['time_start_at'] = '12:01';
        $this->postJson('/bugfix/auction-validation', $data)->assertJsonPath('success', true);
        $this->postJson('/bugfix/auction-validation', array_replace($data, ['timezone' => 'not/a-zone']))
            ->assertOk()->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['timezone']]);
    }
}
