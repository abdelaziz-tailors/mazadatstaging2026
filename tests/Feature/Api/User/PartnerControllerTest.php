<?php

namespace Tests\Feature\Api\User;

use App\Models\LiveVideo;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * GET /api/user/partners lists active vendor accounts (organizers, in this
 * app's user_type naming) for the mobile app. Each partner now carries a
 * real, live-computed "active_auctions_count" integer — the number of
 * auctions that partner currently has in progress (live_videos.status =
 * 'start', LiveVideo.user_id = the partner) — matching the exact semantics
 * BalanceResource already uses for a vendor account's active_bids_count.
 */
class PartnerControllerTest extends TestCase
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

    private function createVendor(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Vendor',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
            'is_active' => 1,
        ], $overrides));
    }

    public function test_index_does_not_require_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/partners');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_active_auctions_count_counts_only_this_partners_live_auctions()
    {
        $partner = $this->createVendor(['name' => 'Partner With Two Live']);
        $otherPartner = $this->createVendor(['name' => 'Other Partner']);

        LiveVideo::create(['title' => 'Mine, live 1', 'status' => 'start', 'user_id' => $partner->id]);
        LiveVideo::create(['title' => 'Mine, live 2', 'status' => 'start', 'user_id' => $partner->id]);
        LiveVideo::create(['title' => 'Mine, ended', 'status' => 'end', 'user_id' => $partner->id]);
        LiveVideo::create(['title' => 'Someone else, live', 'status' => 'start', 'user_id' => $otherPartner->id]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/partners');

        $response->assertStatus(200);
        $data = collect($response->json('data'));

        $this->assertSame(2, $data->firstWhere('id', $partner->id)['active_auctions_count']);
        $this->assertSame(1, $data->firstWhere('id', $otherPartner->id)['active_auctions_count']);
    }

    public function test_active_auctions_count_is_zero_when_the_partner_has_no_live_auctions()
    {
        $partner = $this->createVendor();
        LiveVideo::create(['title' => 'Ended', 'status' => 'end', 'user_id' => $partner->id]);
        LiveVideo::create(['title' => 'Upcoming', 'status' => null, 'user_id' => $partner->id]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/partners');

        $response->assertStatus(200);
        $data = collect($response->json('data'));

        $this->assertSame(0, $data->firstWhere('id', $partner->id)['active_auctions_count']);
    }

    public function test_active_auctions_count_ignores_auctions_belonging_to_other_partners()
    {
        $partner = $this->createVendor();
        $otherPartner = $this->createVendor();

        LiveVideo::create(['title' => 'Not mine', 'status' => 'start', 'user_id' => $otherPartner->id]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/partners');

        $response->assertStatus(200);
        $data = collect($response->json('data'));

        $this->assertSame(0, $data->firstWhere('id', $partner->id)['active_auctions_count']);
    }

    public function test_index_only_lists_active_vendor_accounts()
    {
        $activeVendor = $this->createVendor(['is_active' => 1]);
        $inactiveVendor = $this->createVendor(['is_active' => 0]);
        $buyer = User::create([
            'name' => 'Buyer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
            'is_active' => 1,
        ]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/partners');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($activeVendor->id));
        $this->assertFalse($ids->contains($inactiveVendor->id));
        $this->assertFalse($ids->contains($buyer->id));
    }

    public function test_active_auctions_count_is_present_and_an_integer_for_every_partner()
    {
        $partner = $this->createVendor();
        LiveVideo::create(['title' => 'Live', 'status' => 'start', 'user_id' => $partner->id]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/partners');

        $response->assertStatus(200);
        $entry = collect($response->json('data'))->firstWhere('id', $partner->id);

        $this->assertArrayHasKey('active_auctions_count', $entry);
        $this->assertIsInt($entry['active_auctions_count']);
    }
}
