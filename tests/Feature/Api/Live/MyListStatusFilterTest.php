<?php

namespace Tests\Feature\Api\Live;

use App\Models\LiveVideo;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * GET /api/live/my-list?status={inprogress|upcoming|archive} — the
 * organizer's own auctions, optionally filtered into one of three status
 * buckets. Same bucket semantics as the public AuctionSearchController's
 * filter() endpoint (status='start' -> inprogress, status='end' -> archive,
 * null/anything else -> upcoming), but scoped strictly to the caller's own
 * LiveVideo.user_id — never another organizer's auctions.
 */
class MyListStatusFilterTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'en',
        ];
    }

    private function createOrganizer(): User
    {
        return User::create([
            'name' => 'Organizer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
    }

    public function test_status_inprogress_returns_only_the_organizers_live_auctions()
    {
        $organizer = $this->createOrganizer();
        $live = LiveVideo::create(['title' => 'Live now', 'status' => 'start', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Ended', 'status' => 'end', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Upcoming', 'status' => null, 'user_id' => $organizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list?status=inprogress');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$live->id], $ids->values()->all());
    }

    public function test_status_archive_returns_only_the_organizers_ended_auctions()
    {
        $organizer = $this->createOrganizer();
        LiveVideo::create(['title' => 'Live now', 'status' => 'start', 'user_id' => $organizer->id]);
        $ended = LiveVideo::create(['title' => 'Ended', 'status' => 'end', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Upcoming', 'status' => null, 'user_id' => $organizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list?status=archive');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$ended->id], $ids->values()->all());
    }

    public function test_status_upcoming_returns_auctions_with_a_null_or_other_status()
    {
        $organizer = $this->createOrganizer();
        LiveVideo::create(['title' => 'Live now', 'status' => 'start', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Ended', 'status' => 'end', 'user_id' => $organizer->id]);
        $upcomingNull = LiveVideo::create(['title' => 'Upcoming (null)', 'status' => null, 'user_id' => $organizer->id]);
        $upcomingOther = LiveVideo::create(['title' => 'Upcoming (scheduled)', 'status' => 'scheduled', 'user_id' => $organizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list?status=upcoming');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id')->sort()->values();
        $this->assertEquals(collect([$upcomingNull->id, $upcomingOther->id])->sort()->values()->all(), $ids->all());
    }

    public function test_no_status_filter_returns_all_of_the_organizers_auctions_regardless_of_status()
    {
        $organizer = $this->createOrganizer();
        LiveVideo::create(['title' => 'Live now', 'status' => 'start', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Ended', 'status' => 'end', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Upcoming', 'status' => null, 'user_id' => $organizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_status_filter_fails_validation_for_an_invalid_value()
    {
        $organizer = $this->createOrganizer();
        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list?status=not_a_real_status');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_status_filter_never_leaks_another_organizers_auctions()
    {
        $organizer = $this->createOrganizer();
        $otherOrganizer = $this->createOrganizer();
        LiveVideo::create(['title' => 'Not mine, live', 'status' => 'start', 'user_id' => $otherOrganizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list?status=inprogress');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_my_list_requires_authentication()
    {
        // This route sits inside the auth:api middleware group (unlike
        // /api/user/balance's manual auth('api')->user() check), so an
        // unauthenticated request never reaches the controller at all.
        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list?status=inprogress');

        $response->assertStatus(401);
    }
}
