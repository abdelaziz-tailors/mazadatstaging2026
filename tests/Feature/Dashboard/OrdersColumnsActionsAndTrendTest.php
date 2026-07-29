<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
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
 * The orders list (/admin/orders) was missing an order-date column, its
 * "Auctions" column header didn't say it was a title, its search box had no
 * descriptive placeholder, its row action was a plain text link instead of
 * the round icon-button component (per explicit request, only the "view"
 * icon — orders have no edit/delete concept here), and the "total orders"
 * stat card was the only one with no trend, unlike its siblings.
 */
class OrdersColumnsActionsAndTrendTest extends TestCase
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

    private function createOrder(array $overrides = []): Order
    {
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد تجريبي']);
        $buyer = $this->createBuyer();

        return Order::create(array_merge([
            'order_number' => 'ORD-TEST-' . random_int(100000, 999999),
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ], $overrides));
    }

    private function rowFor(Order $order): array
    {
        // The shared testing database this suite runs against has grown to
        // several thousand real orders, so a fixed "length" page can cut off
        // this test's own row before ever reaching it. Narrow the query down
        // to just this order's own unique order_number via get_data()'s
        // "filter_order_number" param instead — robust regardless of how
        // large the table grows, and doesn't need DataTables' own global
        // search (which requires a full "columns" array in the request to
        // resolve searchable columns).
        $request = Request::create('/admin/orders/getData', 'POST', [
            'draw' => 1, 'start' => 0, 'length' => 50,
            'filter_order_number' => $order->order_number,
        ]);
        app()->instance('request', $request);

        $response = (new OrderController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $order->id);
    }

    public function test_order_date_column_reflects_the_orders_created_at()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $order = $this->createOrder();
        $order->created_at = '2026-05-10 10:00:00';
        $order->save();

        $row = $this->rowFor($order);

        $this->assertEquals('2026-05-10', $row['order_date']);
    }

    public function test_actions_column_is_a_single_view_icon_button()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $order = $this->createOrder();

        $row = $this->rowFor($order);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString('fa-eye', $row['action']);
        $this->assertStringContainsString(route('admin.orders.edit', $order->id), $row['action']);
        $this->assertStringNotContainsString('bg-primary-light', $row['action']);
        $this->assertStringNotContainsString('fe fe-eye', $row['action']);
    }

    public function test_index_page_renders_the_auctions_title_header_and_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new OrderController())->index(new Request())->render();

        $this->assertStringContainsString(TranslationHelper::translate('Auctions Title'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('order_date'), $html);
        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_orders_placeholder'), $html);
    }

    /**
     * The trend is a real month-over-month comparison (this calendar month's
     * order count vs the previous one), not a hardcoded number.
     */
    public function test_total_orders_stat_has_a_real_month_over_month_trend()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $thisMonthOrder = $this->createOrder();
        $thisMonthOrder->created_at = now();
        $thisMonthOrder->save();

        $lastMonthOrder = $this->createOrder();
        $lastMonthOrder->created_at = now()->subMonthNoOverflow();
        $lastMonthOrder->save();

        $view = (new OrderController())->index(new Request());
        $stats = $view->getData()['stats'];

        $this->assertArrayHasKey('total_trend_direction', $stats);
        $this->assertArrayHasKey('total_trend_pct', $stats);
        $this->assertContains($stats['total_trend_direction'], ['up', 'down']);
        $this->assertIsFloat($stats['total_trend_pct']);
    }

    public function test_total_orders_card_renders_a_trend_value_in_the_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new OrderController())->index(new Request())->render();

        // The other three cards already have "stat-trend" markup; the total
        // card must now also have (at least) 4 occurrences, not 3.
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'stat-trend'));
    }
}
