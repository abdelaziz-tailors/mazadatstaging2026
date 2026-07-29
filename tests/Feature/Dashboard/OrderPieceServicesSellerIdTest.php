<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\OrderController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemService;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Bug: /admin/orders/{id}/edit's "خدمات القطعة" (item services) section
 * disappeared ENTIRELY for a piece belonging to the organizer himself —
 * not just the add/remove controls, the whole section, heading included.
 *
 * Root cause: _piece_services.blade.php filtered order items by
 * `$orderItem->liveVideoItem?->seller_id`, which is null for a self-owned
 * piece that was never given an explicit seller_id. OrderItem.seller_id
 * (which OrderService::attachWonItem() always resolves, falling back to
 * the item's own user_id — the organizer) is the authoritative field —
 * same root cause already fixed in
 * OrderService::sellerInvoiceSummariesForOrder()/ForLiveVideo().
 *
 * Separately covers the (correct, unrelated, already-working) editability
 * rule: adding/removing a piece service is only allowed while the order is
 * still unpaid, not delivered, and not yet settled — enforced both in this
 * view and server-side in PieceServiceService::orderIsEditable().
 */
class OrderPieceServicesSellerIdTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * These controllers are called directly (bypassing the HTTP route/
     * LaravelLocalization middleware that normally sets the locale from the
     * /ar/admin/... URL prefix in production), so the locale must be set
     * explicitly — otherwise item titles/custom service names fall back to
     * config('app.locale') = 'en'.
     */
    protected function setUp(): void
    {
        parent::setUp();
        App::setLocale('ar');
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
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

    /**
     * Mirrors exactly how OrderService::attachWonItem() resolves a
     * self-owned piece in real data: the LiveVideoItem is never given an
     * explicit seller_id, and OrderItem.seller_id falls back to the item's
     * own user_id (the organizer).
     */
    private function createOrderWithOwnPiece(array $orderOverrides = []): array
    {
        $organizer = $this->createOrganizer();
        $buyer = $this->createBuyer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد', 'user_id' => $organizer->id]);

        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'title' => 'Own Item', 'title_ar' => 'قطعتي',
            'finished_price' => 500,
            'seller_id' => null,
            'user_id' => $organizer->id,
            'user_finished_id' => $buyer->id,
        ]);

        $order = Order::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 500,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ], $orderOverrides));

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => $liveVideoItem->seller_id ?? $liveVideoItem->user_id,
            'finished_price' => 500,
        ]);

        return [$order, $liveVideoItem, $orderItem];
    }

    public function test_item_services_section_shows_for_a_piece_with_no_explicit_seller_id_on_the_live_video_item()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        [$order] = $this->createOrderWithOwnPiece();

        $html = (new OrderController())->edit($order->id)->render();

        $this->assertStringContainsString(TranslationHelper::translate('item_services'), $html);
        $this->assertStringContainsString('قطعتي', $html);
    }

    public function test_add_service_form_shows_when_the_order_is_still_unpaid()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        [$order] = $this->createOrderWithOwnPiece(['payment_status' => 'unpaid']);

        $html = (new OrderController())->edit($order->id)->render();

        $this->assertStringContainsString(route('admin.order-piece-services.store', $order->id), $html);
        $this->assertStringNotContainsString(TranslationHelper::translate('order_not_editable'), $html);
    }

    public function test_add_service_form_is_hidden_once_the_order_is_paid()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        [$order] = $this->createOrderWithOwnPiece(['payment_status' => 'paid']);

        $html = (new OrderController())->edit($order->id)->render();

        $this->assertStringContainsString(TranslationHelper::translate('item_services'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('order_not_editable'), $html);
        $this->assertStringNotContainsString(route('admin.order-piece-services.store', $order->id), $html);
    }

    public function test_existing_services_still_display_for_an_own_piece_even_when_order_is_paid()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        [$order, , $orderItem] = $this->createOrderWithOwnPiece(['payment_status' => 'paid']);
        OrderItemService::create([
            'order_item_id' => $orderItem->id,
            'price' => 30,
            'custom_name' => json_encode(['ar' => 'حلاقة', 'en' => 'Shearing']),
        ]);

        $html = (new OrderController())->edit($order->id)->render();

        $this->assertStringContainsString('حلاقة', $html);
        $this->assertStringContainsString('30.00', $html);
    }

    /**
     * The item still legitimately shows in the general "items" table further
     * down the same edit page — this renders _piece_services in isolation
     * (as the controller's own $sellerInvoices/$itemServices data would feed
     * it) so the assertion is scoped to just this section, not the whole
     * page.
     */
    public function test_section_is_absent_when_the_order_has_no_seller_id_at_all()
    {
        $buyer = $this->createBuyer();
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد']);
        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id, 'title_ar' => 'قطعة بلا بائع',
            'finished_price' => 200, 'user_finished_id' => $buyer->id,
        ]);
        $order = Order::create([
            'live_video_id' => $liveVideo->id, 'buyer_id' => $buyer->id,
            'total' => 200, 'payment_status' => 'unpaid', 'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'live_video_item_id' => $liveVideoItem->id,
            'seller_id' => null, 'finished_price' => 200,
        ]);

        $html = view('dashboard.pages.orders._piece_services', [
            'order' => $order->fresh(['items.liveVideoItem', 'items.services']),
            'itemServices' => collect(),
        ])->render();

        $this->assertStringNotContainsString('قطعة بلا بائع', $html);
        $this->assertStringNotContainsString(TranslationHelper::translate('item_services'), $html);
    }
}
