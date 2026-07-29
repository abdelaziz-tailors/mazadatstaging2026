<?php

namespace Tests\Feature\Api\User\Profile;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OrganizerStatsControllerTest extends TestCase
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

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    private function createOrder(LiveVideo $liveVideo, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $this->createUser()->id,
            'total' => 100,
            'commission_value' => 0,
            'payment_status' => 'unpaid',
        ], $overrides));
    }

    private function createItem(LiveVideo $liveVideo, array $overrides = []): LiveVideoItem
    {
        return LiveVideoItem::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'title' => 'Item',
        ], $overrides));
    }

    public function test_organizer_stats_endpoint_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_organizer_stats_endpoint_rejects_non_organizer_user_types()
    {
        $buyer = $this->createUser(['user_type' => 'buyer']);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(403)->assertJson(['success' => false, 'code' => 403]);
    }

    public function test_organizer_stats_endpoint_rejects_seller_user_type()
    {
        $seller = $this->createUser(['user_type' => 'seller']);
        Passport::actingAs($seller, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(403)->assertJson(['success' => false, 'code' => 403]);
    }

    public function test_total_buyers_counts_distinct_buyers_of_the_organizers_own_auctions()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        $myAuction = LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);
        $myOtherAuction = LiveVideo::create(['title' => 'Mine too', 'user_id' => $organizer->id]);
        $otherAuction = LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        $buyerA = $this->createUser();
        $buyerB = $this->createUser();

        $this->createOrder($myAuction, ['buyer_id' => $buyerA->id]);
        $this->createOrder($myAuction, ['buyer_id' => $buyerB->id]);
        // Same buyer ordering on a second auction of mine should only count once.
        $this->createOrder($myOtherAuction, ['buyer_id' => $buyerA->id]);
        // Order on someone else's auction must not count towards my buyers.
        $this->createOrder($otherAuction, ['buyer_id' => $this->createUser()->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.total_buyers', 2);
    }

    public function test_pending_commissions_sums_commission_value_on_unpaid_orders_only()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $auction = LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);

        $this->createOrder($auction, ['commission_value' => 100, 'payment_status' => 'unpaid']);
        $this->createOrder($auction, ['commission_value' => 50, 'payment_status' => 'unpaid']);
        $this->createOrder($auction, ['commission_value' => 999, 'payment_status' => 'paid']);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.pending_commissions', 150);
    }

    public function test_monthly_profit_sums_commission_value_on_paid_orders_created_this_month_only()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $auction = LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);

        $thisMonthPaid = $this->createOrder($auction, ['commission_value' => 200, 'payment_status' => 'paid']);
        $thisMonthUnpaid = $this->createOrder($auction, ['commission_value' => 500, 'payment_status' => 'unpaid']);
        $lastMonthPaid = $this->createOrder($auction, ['commission_value' => 300, 'payment_status' => 'paid']);
        $lastMonthPaid->created_at = now()->subMonthNoOverflow();
        $lastMonthPaid->save();

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.monthly_profit', 200);
    }

    public function test_stats_are_scoped_to_the_authenticated_organizers_own_auctions_only()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);
        $otherAuction = LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        $this->createOrder($otherAuction, ['commission_value' => 1000, 'payment_status' => 'paid']);
        $this->createOrder($otherAuction, ['commission_value' => 1000, 'payment_status' => 'unpaid']);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_buyers', 0)
            ->assertJsonPath('data.pending_commissions', 0)
            ->assertJsonPath('data.monthly_profit', 0)
            ->assertJsonPath('data.total_sellers', 0)
            ->assertJsonPath('data.total_auctions', 1)
            ->assertJsonPath('data.total_products', 0)
            ->assertJsonPath('data.total_sales', 0);
    }

    public function test_total_sellers_counts_distinct_sellers_supplying_items_to_the_organizers_own_auctions()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        $myAuction = LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);
        $otherAuction = LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        $sellerA = $this->createUser(['user_type' => 'seller']);
        $sellerB = $this->createUser(['user_type' => 'seller']);

        $this->createItem($myAuction, ['seller_id' => $sellerA->id]);
        $this->createItem($myAuction, ['seller_id' => $sellerB->id]);
        // Same seller supplying a second item should only count once.
        $this->createItem($myAuction, ['seller_id' => $sellerA->id]);
        // An item with no seller_id set must not count.
        $this->createItem($myAuction, ['seller_id' => null]);
        // A seller on someone else's auction must not count towards mine.
        $this->createItem($otherAuction, ['seller_id' => $this->createUser(['user_type' => 'seller'])->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.total_sellers', 2);
    }

    public function test_total_auctions_counts_only_this_organizers_own_auctions()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        LiveVideo::create(['title' => 'Mine 1', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Mine 2', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.total_auctions', 2);
    }

    public function test_total_products_counts_items_across_all_of_the_organizers_own_auctions()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        $myAuctionA = LiveVideo::create(['title' => 'Mine A', 'user_id' => $organizer->id]);
        $myAuctionB = LiveVideo::create(['title' => 'Mine B', 'user_id' => $organizer->id]);
        $otherAuction = LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        $this->createItem($myAuctionA);
        $this->createItem($myAuctionA);
        $this->createItem($myAuctionB);
        $this->createItem($otherAuction);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.total_products', 3);
    }

    public function test_total_sales_sums_finished_price_of_items_across_the_organizers_own_auctions()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        $myAuction = LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);
        $otherAuction = LiveVideo::create(['title' => 'Not mine', 'user_id' => $otherOrganizer->id]);

        $this->createItem($myAuction, ['finished_price' => 150.5]);
        $this->createItem($myAuction, ['finished_price' => 99.25]);
        // Unsold item (null finished_price) must not affect the sum.
        $this->createItem($myAuction, ['finished_price' => null]);
        // Sale on someone else's auction must not count towards mine.
        $this->createItem($otherAuction, ['finished_price' => 5000]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.total_sales', 249.75);
    }

    public function test_buyer_vendor_organizer_type_is_allowed_and_returns_stats()
    {
        $organizer = $this->createUser(['user_type' => 'buyer_vendor']);
        $auction = LiveVideo::create(['title' => 'Mine', 'user_id' => $organizer->id]);

        $this->createOrder($auction, ['commission_value' => 75, 'payment_status' => 'paid']);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/organizer-stats');

        $response->assertStatus(200)->assertJsonPath('data.monthly_profit', 75);
    }
}
