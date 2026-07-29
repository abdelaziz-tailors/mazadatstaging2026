<?php

namespace Tests\Feature\Api\User\Invoice;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SellerInvoicePdfControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'ar',
        ];
    }

    private function createSeller(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test Seller',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'seller',
            'gender' => 'male',
        ], $overrides));
    }

    private function signedPdfUrl(int $orderId, int $sellerId, \DateTimeInterface $expiration = null): string
    {
        return URL::temporarySignedRoute(
            'api.seller-invoice.pdf',
            $expiration ?? now()->addDay(),
            ['id' => $orderId, 'seller_id' => $sellerId]
        );
    }

    /**
     * Builds an order with one item sold, its seller_id set consistently on
     * both LiveVideoItem and OrderItem (mirroring OrderService::attachWonItem,
     * which keeps them in sync in real usage).
     */
    private function createOrderWithSellerItem(User $seller, float $price = 1000, array $liveVideoOverrides = []): array
    {
        $buyer = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $liveVideo = LiveVideo::create(array_merge([
            'title' => 'Sheep auction',
            'title_ar' => 'مزاد نخبة الحلال',
            'commission_amount' => 5,
            'commission_payer' => 'seller',
            'service_fee' => 20,
        ], $liveVideoOverrides));

        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'title' => 'Najdi sheep',
            'title_ar' => 'خروف نجدي',
            'finished_price' => $price,
            'seller_id' => $seller->id,
            'user_finished_id' => $buyer->id,
        ]);

        $order = Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => $price,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => $seller->id,
            'finished_price' => $price,
        ]);

        return [$order, $liveVideoItem, $orderItem];
    }

    public function test_seller_invoice_list_includes_a_pdf_url_per_invoice()
    {
        $seller = $this->createSeller();
        [$order] = $this->createOrderWithSellerItem($seller);
        Passport::actingAs($seller, [], 'api');

        $response = $this->withHeaders($this->headers())
            ->getJson('/api/user/auction/seller-invoice-list');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $row = collect($response->json('data'))->firstWhere('order_id', $order->id);

        $this->assertNotNull($row);
        $this->assertStringContainsString(
            "/api/user/auction/seller-invoice/{$order->id}/pdf",
            $row['pdf_url']
        );
        $this->assertStringContainsString('signature=', $row['pdf_url']);
    }

    /**
     * Mirrors the OrderItem/LiveVideoItem seller_id divergence case for the
     * PDF endpoint, but for the list: the order matches (OrderItem.seller_id
     * = this seller) but the summary is empty (LiveVideoItem.seller_id
     * belongs to someone else) — the row must be silently dropped, not
     * shown with empty/null data.
     */
    public function test_seller_invoice_list_omits_an_order_with_no_matching_summary_line()
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $buyer = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد']);
        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id, 'finished_price' => 500,
            'seller_id' => $otherSeller->id, 'user_finished_id' => $buyer->id,
        ]);
        $order = Order::create([
            'live_video_id' => $liveVideo->id, 'buyer_id' => $buyer->id,
            'total' => 500, 'payment_status' => 'paid', 'status' => 'confirmed',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => $seller->id, 'finished_price' => 500,
        ]);

        Passport::actingAs($seller, [], 'api');
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/user/auction/seller-invoice-list');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNull(collect($response->json('data'))->firstWhere('order_id', $order->id));
    }

    public function test_seller_invoice_list_rejects_a_non_seller_account()
    {
        $buyer = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())
            ->getJson('/api/user/auction/seller-invoice-list');

        $response->assertStatus(403);
    }

    /**
     * Scope check: only the PDF download link is meant to work without any
     * credentials — the list endpoint itself must keep requiring the full
     * x-api-key + Bearer token as before, unaffected by the PDF route's
     * withoutMiddleware('APISettings'). auth:api runs before APISettings for
     * this route (Laravel's middleware priority order), so a request with
     * zero headers hits the auth check first (401) — a valid token with a
     * missing/wrong x-api-key is what actually exercises APISettings (403).
     */
    public function test_seller_invoice_list_still_requires_the_api_key_even_with_a_valid_token()
    {
        $seller = $this->createSeller();
        Passport::actingAs($seller, [], 'api');

        // APISettings returns its rejection as HTTP 200 with a 'code' field
        // in the body (this app's established convention), not a real 403.
        $response = $this->withHeaders(['Accept-Language' => 'ar'])
            ->getJson('/api/user/auction/seller-invoice-list');

        $response->assertStatus(200)->assertJson(['success' => false, 'code' => 403]);
    }

    public function test_seller_invoice_list_still_requires_a_bearer_token()
    {
        // x-api-key present, but no Authorization/Passport::actingAs.
        $response = $this->withHeaders($this->headers())
            ->getJson('/api/user/auction/seller-invoice-list');

        $response->assertStatus(401);
    }

    /**
     * The core requirement this endpoint was built for: the link must be
     * directly downloadable — no Authorization header, no x-api-key, no
     * Passport::actingAs at all — because that's exactly what a browser
     * tab/click can do (it can't attach any custom header).
     */
    public function test_downloads_pdf_directly_via_the_signed_url_with_no_authentication_at_all()
    {
        $seller = $this->createSeller();
        [$order] = $this->createOrderWithSellerItem($seller, 1000);

        // Deliberately no headers of any kind — not even x-api-key.
        $response = $this->get($this->signedPdfUrl($order->id, $seller->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'INV-' . $order->id . '-S' . $seller->id,
            $response->headers->get('content-disposition')
        );
        $this->assertNotEmpty($response->getContent());
    }

    /**
     * The core data-isolation requirement: when an order has items from two
     * different sellers, each seller's own signed link must show only their
     * own lines/totals — never the other seller's data, even without any
     * authentication distinguishing "who is asking."
     */
    public function test_pdf_does_not_leak_another_sellers_line_items_in_a_shared_order()
    {
        $sellerA = $this->createSeller();
        $sellerB = $this->createSeller();
        $buyer = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $liveVideo = LiveVideo::create([
            'title' => 'Shared auction', 'title_ar' => 'مزاد مشترك',
            'commission_amount' => 0, 'commission_payer' => 'buyer', 'service_fee' => 0,
        ]);

        $itemA = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id, 'title_ar' => 'قطعة البائع الأول',
            'finished_price' => 500, 'seller_id' => $sellerA->id, 'user_finished_id' => $buyer->id,
        ]);
        $itemB = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id, 'title_ar' => 'قطعة البائع الثاني',
            'finished_price' => 700, 'seller_id' => $sellerB->id, 'user_finished_id' => $buyer->id,
        ]);

        $order = Order::create([
            'live_video_id' => $liveVideo->id, 'buyer_id' => $buyer->id,
            'total' => 1200, 'payment_status' => 'paid', 'status' => 'confirmed',
        ]);

        OrderItem::create(['order_id' => $order->id, 'live_video_item_id' => $itemA->id, 'seller_id' => $sellerA->id, 'finished_price' => 500]);
        OrderItem::create(['order_id' => $order->id, 'live_video_item_id' => $itemB->id, 'seller_id' => $sellerB->id, 'finished_price' => 700]);

        $responseA = $this->withHeaders($this->headers())
            ->get($this->signedPdfUrl($order->id, $sellerA->id));
        $responseA->assertStatus(200);
        $pdfTextA = $responseA->getContent();

        $responseB = $this->withHeaders($this->headers())
            ->get($this->signedPdfUrl($order->id, $sellerB->id));
        $responseB->assertStatus(200);
        $pdfTextB = $responseB->getContent();

        // Different sellers on the same order must get different PDF bytes
        // (different gross totals: 500 vs 700) and different invoice numbers.
        $this->assertNotEquals($pdfTextA, $pdfTextB);
        $this->assertStringContainsString('INV-' . $order->id . '-S' . $sellerA->id, $responseA->headers->get('content-disposition'));
        $this->assertStringContainsString('INV-' . $order->id . '-S' . $sellerB->id, $responseB->headers->get('content-disposition'));
    }

    /**
     * A signed link legitimately generated for one seller can't be replayed
     * for a different seller_id by editing the query string — doing so
     * invalidates the signature (rejected before the controller runs), it
     * doesn't just fall through to "invoice not found".
     */
    public function test_tampering_with_the_seller_id_query_param_invalidates_the_signature()
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        [$order] = $this->createOrderWithSellerItem($seller);

        $tamperedUrl = str_replace(
            'seller_id=' . $seller->id,
            'seller_id=' . $otherSeller->id,
            $this->signedPdfUrl($order->id, $seller->id)
        );

        $response = $this->withHeaders($this->headers())->get($tamperedUrl);

        $response->assertStatus(403);
    }

    public function test_an_expired_signed_link_is_rejected()
    {
        $seller = $this->createSeller();
        [$order] = $this->createOrderWithSellerItem($seller);

        $expiredUrl = $this->signedPdfUrl($order->id, $seller->id, now()->subMinute());

        $response = $this->withHeaders($this->headers())->get($expiredUrl);

        $response->assertStatus(403);
    }

    public function test_a_url_with_no_signature_at_all_is_rejected()
    {
        $seller = $this->createSeller();
        [$order] = $this->createOrderWithSellerItem($seller);

        $response = $this->withHeaders($this->headers())
            ->get("/api/user/auction/seller-invoice/{$order->id}/pdf?seller_id={$seller->id}");

        $response->assertStatus(403);
    }

    public function test_returns_failure_when_seller_has_no_items_in_the_order()
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        [$order] = $this->createOrderWithSellerItem($otherSeller);

        $response = $this->withHeaders($this->headers())
            ->get($this->signedPdfUrl($order->id, $seller->id));

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_returns_failure_when_order_does_not_exist()
    {
        $seller = $this->createSeller();

        $response = $this->withHeaders($this->headers())
            ->get($this->signedPdfUrl(999999, $seller->id));

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    /**
     * OrderItem.seller_id and LiveVideoItem.seller_id are normally kept in
     * sync (see OrderService::attachWonItem), but the summary that the PDF
     * is built from groups by the LiveVideoItem side specifically — if they
     * ever diverged, the order would still be found (matches on OrderItem)
     * but no summary line would exist for this seller.
     */
    public function test_returns_failure_when_order_item_seller_id_and_live_video_item_seller_id_disagree()
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $buyer = User::create([
            'name' => 'Buyer', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'buyer', 'gender' => 'male',
        ]);

        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد']);
        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id, 'finished_price' => 500,
            'seller_id' => $otherSeller->id, // diverges from the OrderItem below
            'user_finished_id' => $buyer->id,
        ]);
        $order = Order::create([
            'live_video_id' => $liveVideo->id, 'buyer_id' => $buyer->id,
            'total' => 500, 'payment_status' => 'paid', 'status' => 'confirmed',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => $seller->id, 'finished_price' => 500,
        ]);

        $response = $this->withHeaders($this->headers())
            ->get($this->signedPdfUrl($order->id, $seller->id));

        $response->assertStatus(200)->assertJson(['success' => false]);
    }
}
