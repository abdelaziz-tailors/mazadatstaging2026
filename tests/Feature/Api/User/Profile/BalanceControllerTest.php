<?php

namespace Tests\Feature\Api\User\Profile;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User\User;
use App\Models\VideoComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BalanceControllerTest extends TestCase
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
            'wallet_balance' => 500,
        ], $overrides));
    }

    public function test_balance_endpoint_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_balance_endpoint_returns_wallet_balance_and_dues()
    {
        $user = $this->createUser(['wallet_balance' => 1250.5]);
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.balance', 1250.5)
            ->assertJsonPath('data.dues', 0);
    }

    /**
     * available_balance/pending_balance are explicit aliases of
     * balance/dues, added so the app never has to derive "available" itself
     * (e.g. via balance - dues, which goes negative whenever dues exceed the
     * current balance — pending dues aren't a deduction from the wallet,
     * they just haven't been credited to it yet).
     */
    public function test_balance_endpoint_returns_available_and_pending_balance_aliases()
    {
        $seller = $this->createUser(['user_type' => 'seller', 'wallet_balance' => 0]);
        $buyer = $this->createUser(['user_type' => 'buyer']);
        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'commission_amount' => 0,
            'commission_payer' => 'buyer',
            'service_fee' => 0,
        ]);
        $order = Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 2270,
            'payment_status' => 'paid',
        ]);
        $item = LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 2270]);
        OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $item->id,
            'seller_id' => $seller->id,
            'finished_price' => 2270,
            'settled_at' => null,
        ]);

        Passport::actingAs($seller, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        // available_balance is never derived as balance - pending (which
        // would go negative here, -2270) — it's always just the real
        // wallet_balance (0, nothing settled into the wallet yet).
        $response->assertStatus(200)
            ->assertJsonPath('data.balance', 0)
            ->assertJsonPath('data.dues', 2270)
            ->assertJsonPath('data.available_balance', 0)
            ->assertJsonPath('data.pending_balance', 2270);
    }

    public function test_active_bids_count_counts_the_organizers_own_active_auctions()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $otherOrganizer = $this->createUser(['user_type' => 'vendor']);

        LiveVideo::create(['title' => 'Mine, live', 'status' => 'start', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Mine, live 2', 'status' => 'start', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Mine, ended', 'status' => 'end', 'user_id' => $organizer->id]);
        LiveVideo::create(['title' => 'Someone else, live', 'status' => 'start', 'user_id' => $otherOrganizer->id]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.active_bids_count', 2);
    }

    public function test_active_bids_count_counts_active_auctions_the_buyer_has_placed_a_bid_in()
    {
        $buyer = $this->createUser(['user_type' => 'buyer']);
        $otherBuyer = $this->createUser(['user_type' => 'buyer']);

        $auctionIBidOn = LiveVideo::create(['title' => 'Live, I bid', 'status' => 'start']);
        $auctionIDidNotBidOn = LiveVideo::create(['title' => 'Live, not mine', 'status' => 'start']);
        $endedAuctionIBidOn = LiveVideo::create(['title' => 'Ended, I bid', 'status' => 'end']);

        VideoComment::create(['video_id' => $auctionIBidOn->id, 'user_id' => $buyer->id, 'comment' => '100']);
        VideoComment::create(['video_id' => $auctionIDidNotBidOn->id, 'user_id' => $otherBuyer->id, 'comment' => '50']);
        VideoComment::create(['video_id' => $endedAuctionIBidOn->id, 'user_id' => $buyer->id, 'comment' => '75']);

        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.active_bids_count', 1);
    }

    public function test_active_bids_count_counts_active_auctions_with_the_sellers_items()
    {
        $seller = $this->createUser(['user_type' => 'seller']);
        $otherSeller = $this->createUser(['user_type' => 'seller']);

        $auctionWithMyItem = LiveVideo::create(['title' => 'Live, my item', 'status' => 'start']);
        $auctionWithoutMyItem = LiveVideo::create(['title' => 'Live, not my item', 'status' => 'start']);

        LiveVideoItem::create(['live_video_id' => $auctionWithMyItem->id, 'seller_id' => $seller->id]);
        LiveVideoItem::create(['live_video_id' => $auctionWithoutMyItem->id, 'seller_id' => $otherSeller->id]);

        Passport::actingAs($seller, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.active_bids_count', 1);
    }

    public function test_active_bids_count_for_buyer_vendor_combines_organizer_and_bidder_sides()
    {
        $user = $this->createUser(['user_type' => 'buyer_vendor']);

        $ownedAuction = LiveVideo::create(['title' => 'Mine, live', 'status' => 'start', 'user_id' => $user->id]);
        $biddingAuction = LiveVideo::create(['title' => 'Live, I bid', 'status' => 'start']);

        VideoComment::create(['video_id' => $biddingAuction->id, 'user_id' => $user->id, 'comment' => '100']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.active_bids_count', 2);
    }

    public function test_active_bids_count_is_zero_for_a_buyer_with_no_bids()
    {
        $buyer = $this->createUser(['user_type' => 'buyer']);
        LiveVideo::create(['title' => 'Live, not mine', 'status' => 'start']);

        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.active_bids_count', 0);
    }

    public function test_buyer_dues_sums_unpaid_order_totals()
    {
        $buyer = $this->createUser(['user_type' => 'buyer']);

        Order::create([
            'live_video_id' => LiveVideo::create(['title' => 'Auction 1'])->id,
            'buyer_id' => $buyer->id,
            'total' => 500,
            'payment_status' => 'unpaid',
        ]);
        Order::create([
            'live_video_id' => LiveVideo::create(['title' => 'Auction 2'])->id,
            'buyer_id' => $buyer->id,
            'total' => 300,
            'payment_status' => 'paid',
        ]);

        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.dues', 500);
    }

    public function test_seller_dues_sums_unsettled_net_amounts()
    {
        $seller = $this->createUser(['user_type' => 'seller']);
        $buyer = $this->createUser(['user_type' => 'buyer']);
        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'commission_amount' => 0,
            'commission_payer' => 'buyer',
            'service_fee' => 20,
        ]);
        $order = Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 1000,
            'payment_status' => 'paid',
        ]);

        $unsettledItem = LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 500]);
        OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $unsettledItem->id,
            'seller_id' => $seller->id,
            'finished_price' => 500,
            'settled_at' => null,
        ]);

        $settledItem = LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 400]);
        OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $settledItem->id,
            'seller_id' => $seller->id,
            'finished_price' => 400,
            'settled_at' => now(),
        ]);

        Passport::actingAs($seller, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        // Only the unsettled item counts: 500 (finished_price) - 20 (service fee) = 480.
        $response->assertStatus(200)->assertJsonPath('data.dues', 480);
    }

    public function test_buyer_vendor_dues_combine_buyer_and_seller_sides()
    {
        $user = $this->createUser(['user_type' => 'buyer_vendor']);
        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'commission_amount' => 0,
            'commission_payer' => 'buyer',
            'service_fee' => 0,
        ]);

        Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $user->id,
            'total' => 200,
            'payment_status' => 'unpaid',
        ]);

        $order = Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $this->createUser()->id,
            'total' => 1000,
            'payment_status' => 'paid',
        ]);
        $item = LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 300]);
        OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $item->id,
            'seller_id' => $user->id,
            'finished_price' => 300,
            'settled_at' => null,
        ]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/balance');

        $response->assertStatus(200)->assertJsonPath('data.dues', 500);
    }
}
