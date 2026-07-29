<?php

namespace Tests\Feature\Api\User\Invoice;

use App\Helpers\TranslationHelper;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemService;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * GET /api/user/auction/partner-invoice-items — per explicit request, this
 * returns ONE consolidated invoice per AUCTION (LiveVideo), not per buyer
 * order. A single auction can have several different buyers, each with
 * their own Order row; these are merged into a single invoice with a
 * per-seller breakdown, instead of the organizer seeing one separate
 * invoice per buyer. The invoice's own id is the auction's own id
 * (`auction_id`/`invoice_id`), since there's no longer a single Order to
 * key off of.
 *
 * `payment_status`/`status` are dropped from the top level entirely (they
 * were per-Order concepts, and a single auction can span orders in
 * different payment states) — `orders_count`/`paid_orders_count`/
 * `unpaid_orders_count` are exposed instead.
 *
 * Every line/seller/order-level total also carries `partner_earnings` —
 * what the organizer himself earns:
 *   - normal consignor's line: commission + service_fee + piece_services.
 *   - the organizer's own piece (he is also the seller_id on that lot): the
 *     full sale price, PLUS the service_fee and piece_services that would
 *     otherwise have been deducted from him as if he were a regular
 *     consignor (added on top, not subtracted).
 *
 * The underlying per-line numbers come from a new
 * OrderService::sellerInvoiceSummariesForLiveVideo() (mirrors
 * sellerInvoiceSummariesForOrder()'s math, but consolidated across every
 * Order for the auction) — the original per-order method is untouched and
 * still used by the sibling seller-invoice-items endpoint
 * (formatSellerInvoiceOrder()) and the admin dashboard invoice view.
 */
class PartnerInvoiceItemsTest extends TestCase
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

    private function createOrganizer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Organizer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ], $overrides));
    }

    private function createSeller(): User
    {
        return User::create([
            'name' => 'Seller',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'seller',
            'gender' => 'male',
        ]);
    }

    private function createBuyer(): User
    {
        return User::create([
            'name' => 'Buyer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);
    }

    private function createLiveVideo(User $organizer, array $overrides = []): LiveVideo
    {
        return LiveVideo::create(array_merge([
            'title' => 'Auction',
            'title_ar' => 'مزاد',
            'user_id' => $organizer->id,
            'commission_amount' => 5,
            'commission_payer' => 'seller',
            'service_fee' => 20,
        ], $overrides));
    }

    /**
     * One order, one item, one seller — the simplest building block.
     */
    private function createOrderWithSellerItem(
        User $organizer,
        LiveVideo $liveVideo,
        User $seller,
        float $price = 1000,
        array $orderOverrides = []
    ): array {
        $buyer = $this->createBuyer();

        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'title' => 'Item',
            'title_ar' => 'قطعة',
            'finished_price' => $price,
            'seller_id' => $seller->id,
            'user_id' => $organizer->id,
            'user_finished_id' => $buyer->id,
        ]);

        $order = Order::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => $price,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ], $orderOverrides));

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => $seller->id,
            'finished_price' => $price,
        ]);

        return [$order, $liveVideoItem, $orderItem];
    }

    /**
     * Reproduces exactly how OrderService::attachWonItem() resolves a
     * self-owned piece in real data: the LiveVideoItem is never given an
     * explicit seller_id at all (it's the organizer's own piece, so nothing
     * ever set one), and OrderItem.seller_id falls back to the item's own
     * user_id (the organizer) — never explicitly set to match the
     * organizer's id either, mirroring the fallback logic instead of just
     * asserting it.
     */
    private function createOrderWithOwnPieceMissingLiveVideoItemSellerId(
        User $organizer,
        LiveVideo $liveVideo,
        float $price = 1000
    ): array {
        $buyer = $this->createBuyer();

        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'title' => 'Own Item',
            'title_ar' => 'قطعتي',
            'finished_price' => $price,
            'seller_id' => null,
            'user_id' => $organizer->id,
            'user_finished_id' => $buyer->id,
        ]);

        $order = Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => $price,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => $liveVideoItem->seller_id ?? $liveVideoItem->user_id,
            'finished_price' => $price,
        ]);

        return [$order, $liveVideoItem, $orderItem];
    }

    public function test_response_is_grouped_by_auction_with_the_auctions_id_as_the_invoice_id()
    {
        $organizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer);
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 1000);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);

        $this->assertNotNull($row);
        $this->assertEquals((string) $liveVideo->id, $row['invoice_id']);
        $this->assertArrayNotHasKey('payment_status', $row);
        $this->assertArrayNotHasKey('status', $row);
        $this->assertArrayNotHasKey('order_id', $row);
        $this->assertArrayNotHasKey('order_number', $row);
    }

    public function test_multiple_buyer_orders_for_the_same_auction_are_merged_into_one_invoice()
    {
        $organizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer, ['commission_amount' => 0, 'service_fee' => 0]);

        // Two different buyers, two different Order rows, same auction, same seller.
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 500);
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 300);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');

        $rows = collect($response->json('data'))->where('auction_id', $liveVideo->id);
        $this->assertCount(1, $rows, 'Expected exactly one consolidated invoice for this auction.');

        $row = $rows->first();
        $this->assertEquals(2, $row['orders_count']);
        $this->assertEquals(1, $row['sellers_count']);
        $this->assertEquals(2, $row['items_count']);
        $this->assertEquals(800.0, $row['totals']['gross']);

        $sellerGroup = $row['sellers'][0];
        $this->assertEquals(2, $sellerGroup['items_count']);
        $this->assertEqualsCanonicalizing([500.0, 300.0], collect($sellerGroup['items'])->pluck('price')->all());
    }

    public function test_orders_count_breaks_down_paid_vs_unpaid()
    {
        $organizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer);

        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 500, ['payment_status' => 'paid', 'status' => 'confirmed']);
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 300, ['payment_status' => 'unpaid']);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);

        $this->assertEquals(2, $row['orders_count']);
        $this->assertEquals(1, $row['paid_orders_count']);
        $this->assertEquals(1, $row['unpaid_orders_count']);
    }

    public function test_line_item_keeps_the_original_flat_fields_and_gains_partner_earnings()
    {
        $organizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer);
        // 5% seller-paid commission + 20 flat service fee, no piece services.
        // net = 1000 - 50 - 20 - 0 = 930, partner_earnings = 50 + 20 + 0 = 70
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 1000);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);
        $line = $row['sellers'][0]['items'][0];

        $this->assertEquals(1000, $line['price']);
        $this->assertEquals(50.0, $line['commission']);
        $this->assertEquals(20.0, $line['service_fee']);
        $this->assertEquals(0.0, $line['piece_services']);
        $this->assertEquals(930.0, $line['net']);
        $this->assertEquals(70.0, $line['partner_earnings']);
    }

    public function test_line_item_partner_earnings_includes_piece_services_for_a_consignor_seller()
    {
        $organizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer, ['commission_amount' => 0, 'commission_payer' => 'buyer', 'service_fee' => 0]);
        [, , $orderItem] = $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 1000);

        OrderItemService::create([
            'order_item_id' => $orderItem->id,
            'price' => 75,
            'custom_name' => json_encode(['ar' => 'خدمة', 'en' => 'Service']),
        ]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);
        $line = $row['sellers'][0]['items'][0];

        $this->assertEquals(75.0, $line['piece_services']);
        $this->assertEquals(925.0, $line['net']);
        $this->assertEquals(75.0, $line['partner_earnings']);
    }

    /**
     * The organizer's own piece: partner_earnings = full price + service_fee
     * + piece_services (added on top, not just the bare price).
     */
    public function test_own_piece_partner_earnings_is_price_plus_service_fee_plus_piece_services()
    {
        $organizer = $this->createOrganizer();
        $liveVideo = $this->createLiveVideo($organizer, ['commission_amount' => 5, 'commission_payer' => 'seller', 'service_fee' => 20]);
        // The organizer is also the seller_id on his own piece here.
        [, , $orderItem] = $this->createOrderWithSellerItem($organizer, $liveVideo, $organizer, 1000);
        OrderItemService::create([
            'order_item_id' => $orderItem->id,
            'price' => 30,
            'custom_name' => json_encode(['ar' => 'خدمة', 'en' => 'Service']),
        ]);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);
        $line = $row['sellers'][0]['items'][0];

        // commission/service_fee/net still reported as computed (unchanged),
        // but partner_earnings = price + service_fee + piece_services = 1000 + 20 + 30 = 1050.
        $this->assertEquals(1000, $line['price']);
        $this->assertEquals(50.0, $line['commission']);
        $this->assertEquals(20.0, $line['service_fee']);
        $this->assertEquals(30.0, $line['piece_services']);
        $this->assertEquals(1050.0, $line['partner_earnings']);
    }

    /**
     * Per explicit request: when a "seller" group is actually the auction
     * owner's own piece, his name is suffixed with "(صاحب المزاد)" so the
     * app can visually distinguish it from a real consignor's group without
     * an extra flag.
     */
    public function test_own_piece_seller_name_is_suffixed_with_auction_owner_label()
    {
        $organizer = $this->createOrganizer(['name' => 'عبدالعزيز جمال']);
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer);
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 500);
        $this->createOrderWithSellerItem($organizer, $liveVideo, $organizer, 400);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);

        $ownGroup = collect($row['sellers'])->firstWhere('seller_id', $organizer->id);
        $consignorGroup = collect($row['sellers'])->firstWhere('seller_id', $seller->id);

        $this->assertEquals('عبدالعزيز جمال (' . TranslationHelper::translate('auction_owner') . ')', $ownGroup['seller_name']);
        $this->assertEquals($seller->name, $consignorGroup['seller_name']);
    }

    public function test_seller_and_order_totals_partner_earnings_mix_own_and_consignor_pieces_correctly()
    {
        $organizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer, ['commission_amount' => 10, 'commission_payer' => 'seller', 'service_fee' => 0]);

        // Consignor seller's piece: partner_earnings = commission = 50.
        $this->createOrderWithSellerItem($organizer, $liveVideo, $seller, 500);
        // Organizer's own piece: partner_earnings = full price = 400.
        $this->createOrderWithSellerItem($organizer, $liveVideo, $organizer, 400);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);

        $ownSellerGroup = collect($row['sellers'])->firstWhere('seller_id', $organizer->id);
        $consignorSellerGroup = collect($row['sellers'])->firstWhere('seller_id', $seller->id);

        $this->assertEquals(400.0, $ownSellerGroup['totals']['partner_earnings']);
        $this->assertEquals(50.0, $consignorSellerGroup['totals']['partner_earnings']);
        $this->assertEquals(450.0, $row['totals']['partner_earnings']);
    }

    public function test_rejects_a_non_organizer_account()
    {
        $buyer = $this->createBuyer();
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');

        $response->assertStatus(403);
    }

    public function test_does_not_include_another_organizers_auctions()
    {
        $organizer = $this->createOrganizer();
        $otherOrganizer = $this->createOrganizer();
        $seller = $this->createSeller();
        $otherLiveVideo = $this->createLiveVideo($otherOrganizer);
        $this->createOrderWithSellerItem($otherOrganizer, $otherLiveVideo, $seller);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');

        $response->assertStatus(200);
        $this->assertNull(collect($response->json('data'))->firstWhere('auction_id', $otherLiveVideo->id));
    }

    /**
     * Bug: an auction with 3 pieces — 2 belonging to other sellers, 1 the
     * organizer's own — only returned 2 lines, silently dropping the
     * organizer's own piece. Root cause: the grouping/filtering used
     * LiveVideoItem.seller_id, which is null for a self-owned piece that
     * was never given an explicit seller_id — OrderItem.seller_id (which
     * attachWonItem() always resolves, falling back to the item's own
     * user_id) is the field that must be used instead.
     */
    public function test_all_three_pieces_return_together_when_the_own_piece_never_got_an_explicit_seller_id()
    {
        $organizer = $this->createOrganizer();
        $sellerA = $this->createSeller();
        $sellerB = $this->createSeller();
        $liveVideo = $this->createLiveVideo($organizer, ['commission_amount' => 0, 'service_fee' => 0]);

        $this->createOrderWithSellerItem($organizer, $liveVideo, $sellerA, 500);
        $this->createOrderWithSellerItem($organizer, $liveVideo, $sellerB, 300);
        $this->createOrderWithOwnPieceMissingLiveVideoItemSellerId($organizer, $liveVideo, 400);

        Passport::actingAs($organizer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/auction/partner-invoice-items');
        $row = collect($response->json('data'))->firstWhere('auction_id', $liveVideo->id);

        $this->assertEquals(3, $row['sellers_count']);
        $this->assertEquals(3, $row['items_count']);

        $ownSellerGroup = collect($row['sellers'])->firstWhere('seller_id', $organizer->id);
        $this->assertNotNull($ownSellerGroup, 'The organizer\'s own piece is missing from the invoice.');
        $this->assertEquals(400.0, $ownSellerGroup['totals']['partner_earnings']);
        $this->assertEquals(1200.0, $row['totals']['gross']);
    }
}
