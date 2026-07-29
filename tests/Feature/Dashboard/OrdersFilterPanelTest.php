<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\OrderController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Covers the orders-page filter panel added on top of
 * OrderController::get_data(): order number, buyer name, status, payment
 * status, and date range filters — the same pattern already built for
 * buyers/vendors/auctions/partners, built from real, already-stored columns
 * (order_number/status/payment_status/created_at + the linked buyer's name),
 * no new schema.
 */
class OrdersFilterPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    private function createBuyer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Buyer ' . random_int(100000, 999999),
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    private function createOrder(array $overrides = []): Order
    {
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد تجريبي']);
        $buyer = $overrides['buyer'] ?? $this->createBuyer();
        unset($overrides['buyer']);

        return Order::create(array_merge([
            'order_number' => 'ORD-TEST-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ], $overrides));
    }

    private function callGetData(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $request = Request::create('/admin/orders/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 5000,
        ], $params));
        app()->instance('request', $request);

        $response = (new OrderController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    /**
     * Random suffixes (not just createOrder()'s own default random suffix)
     * are appended to these literal prefixes because the shared testing
     * database this suite runs against is a real, busy dev DB whose rows
     * persist across separate test runs — a fixed literal like
     * "ORD-UNIQUE-777" passes the first time but collides with its own
     * leftover row (unique constraint) on a rerun.
     */
    public function test_filter_order_number_matches_by_partial_number()
    {
        $suffix = random_int(100000, 999999);
        $match = $this->createOrder(['order_number' => "ORD-UNIQUE-{$suffix}"]);
        $other = $this->createOrder(['order_number' => "ORD-OTHER-{$suffix}"]);

        $ids = $this->callGetData(['filter_order_number' => "UNIQUE-{$suffix}"]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_buyer_matches_by_partial_buyer_name()
    {
        $matchBuyer = $this->createBuyer(['name' => 'Ahmed Special Buyer']);
        $otherBuyer = $this->createBuyer(['name' => 'Someone Else']);
        $match = $this->createOrder(['buyer' => $matchBuyer]);
        $other = $this->createOrder(['buyer' => $otherBuyer]);

        $ids = $this->callGetData(['filter_buyer' => 'Special Buyer']);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_status_matches_exact_status()
    {
        $pending = $this->createOrder(['status' => 'pending']);
        $shipping = $this->createOrder(['status' => 'shipping']);

        $ids = $this->callGetData(['filter_status' => 'shipping']);

        $this->assertFalse($ids->contains($pending->id));
        $this->assertTrue($ids->contains($shipping->id));
    }

    public function test_filter_payment_status_matches_exact_payment_status()
    {
        $paid = $this->createOrder(['payment_status' => 'paid']);
        $unpaid = $this->createOrder(['payment_status' => 'unpaid']);

        $ids = $this->callGetData(['filter_payment_status' => 'paid']);

        $this->assertTrue($ids->contains($paid->id));
        $this->assertFalse($ids->contains($unpaid->id));
    }

    public function test_filter_date_range_excludes_orders_outside_the_range()
    {
        $inRange = $this->createOrder();
        $inRange->created_at = now()->subDays(5);
        $inRange->save();

        $outOfRange = $this->createOrder();
        $outOfRange->created_at = now()->subDays(30);
        $outOfRange->save();

        $ids = $this->callGetData([
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_combined_filters_apply_together_as_an_intersection()
    {
        $suffix = random_int(100000, 999999);
        $match = $this->createOrder(['order_number' => "ORD-COMBO-{$suffix}-1", 'status' => 'pending', 'payment_status' => 'unpaid']);
        $wrongNumber = $this->createOrder(['order_number' => "ORD-NOPE-{$suffix}-1", 'status' => 'pending', 'payment_status' => 'unpaid']);
        $wrongStatus = $this->createOrder(['order_number' => "ORD-COMBO-{$suffix}-2", 'status' => 'shipping', 'payment_status' => 'unpaid']);

        $ids = $this->callGetData([
            'filter_order_number' => "ORD-COMBO-{$suffix}",
            'filter_status' => 'pending',
        ]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongNumber->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
    }

    /**
     * The shared testing database this suite runs against has grown to
     * several thousand real orders — with no explicit "order" param sent,
     * Yajra returns rows in ascending-id order, so a fixed "length" can cut
     * off this test's own (highest-id, most recently created) row long
     * before reaching it. Assert on the real recordsTotal delta instead of
     * "is our row in the page" — matches the same before/after pattern
     * already used by OrderControllerStatsTest for the same reason.
     */
    public function test_no_filters_returns_unfiltered_results()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        $baselineRequest = Request::create('/admin/orders/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 1]);
        app()->instance('request', $baselineRequest);
        $baselineTotal = json_decode((new OrderController())->get_data($baselineRequest)->getContent(), true)['recordsTotal'];

        $this->createOrder();

        $afterRequest = Request::create('/admin/orders/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 1]);
        app()->instance('request', $afterRequest);
        $afterTotal = json_decode((new OrderController())->get_data($afterRequest)->getContent(), true)['recordsTotal'];

        $this->assertEquals($baselineTotal + 1, $afterTotal);
    }

    public function test_filter_panel_markup_is_rendered_on_the_orders_index_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new OrderController())->index(new Request());
        $html = $view->render();

        $this->assertStringContainsString('id="ordersFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_order_number"', $html);
        $this->assertStringContainsString('id="filter_buyer"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_payment_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
        $this->assertStringContainsString('md-wide-search', $html);
    }
}
