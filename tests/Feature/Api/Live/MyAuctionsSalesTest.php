<?php

namespace Tests\Feature\Api\Live;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MyAuctionsSalesTest extends TestCase
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

    public function test_my_list_returns_sales_as_the_sum_of_sold_item_prices()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'My auction', 'user_id' => $organizer->id]);

        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 1000]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 1000]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 1000]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 1000]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $auction = collect($response->json('data'))->firstWhere('id', $liveVideo->id);
        $this->assertNotNull($auction);
        $this->assertEquals(4000, $auction['sales']);
    }

    public function test_my_list_sales_ignores_unsold_items()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'My auction', 'user_id' => $organizer->id]);

        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 1000]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => null]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list');

        $auction = collect($response->json('data'))->firstWhere('id', $liveVideo->id);
        $this->assertEquals(1000, $auction['sales']);
    }

    public function test_my_list_sales_is_zero_when_nothing_sold_yet()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = LiveVideo::create(['title' => 'My auction', 'user_id' => $organizer->id]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => null]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list');

        $auction = collect($response->json('data'))->firstWhere('id', $liveVideo->id);
        $this->assertEquals(0, $auction['sales']);
    }

    public function test_my_list_only_returns_the_authenticated_organizers_own_auctions()
    {
        $organizer = $this->createOrganizer();
        $otherOrganizer = $this->createOrganizer();
        LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/live/my-list');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
