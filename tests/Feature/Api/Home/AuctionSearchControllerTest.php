<?php

namespace Tests\Feature\Api\Home;

use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuctionSearchControllerTest extends TestCase
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

    private function createAuction(array $overrides = []): LiveVideo
    {
        return LiveVideo::create(array_merge([
            'title' => 'Camel auction',
            'title_ar' => 'مزاد الإبل',
            'status' => null,
        ], $overrides));
    }

    public function test_search_returns_matching_auctions_by_english_title()
    {
        $match = $this->createAuction(['title' => 'Sheep flock auction']);
        $this->createAuction(['title' => 'Goat market']);

        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/search?q=Sheep');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    public function test_search_matches_arabic_title()
    {
        $match = $this->createAuction(['title_ar' => 'مزاد الأغنام النادرة']);

        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/search?q=' . urlencode('الأغنام'));

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($match->id));
    }

    public function test_search_returns_empty_data_when_nothing_matches()
    {
        $this->createAuction(['title' => 'Camel auction']);

        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/search?q=zzz_no_match_zzz');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_search_fails_validation_when_query_is_missing()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/search');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_filter_returns_only_inprogress_auctions()
    {
        $inprogress = $this->createAuction(['status' => 'start']);
        $this->createAuction(['status' => 'end']);
        $this->createAuction(['status' => null]);

        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/filter?status=inprogress');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($inprogress->id));
        $this->assertCount(1, $ids);
    }

    public function test_filter_returns_only_archived_auctions()
    {
        $this->createAuction(['status' => 'start']);
        $archived = $this->createAuction(['status' => 'end']);
        $this->createAuction(['status' => null]);

        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/filter?status=archive');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($archived->id));
        $this->assertCount(1, $ids);
    }

    public function test_filter_returns_only_upcoming_auctions()
    {
        $this->createAuction(['status' => 'start']);
        $this->createAuction(['status' => 'end']);
        $upcoming = $this->createAuction(['status' => null]);

        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/filter?status=upcoming');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($upcoming->id));
        $this->assertCount(1, $ids);
    }

    public function test_filter_fails_validation_when_status_is_missing()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/filter');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_filter_fails_validation_when_status_is_invalid()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/home/auctions/filter?status=not_a_real_status');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }
}
