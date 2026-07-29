<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\PartnerFinanceController;
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
 * Covers the invoices-page (/admin/partner-finance/invoices) filter panel
 * added on top of PartnerFinanceController::invoices(): status, payment
 * status, and date range filters, layered onto the existing "search"
 * (order number / buyer name) query filter — the same pattern already built
 * for orders/buyers/vendors/auctions/partners, built from real,
 * already-stored columns (status/payment_status/created_at), no new schema.
 * This page is plain GET-query filtering (no DataTables/AJAX here, unlike
 * the other list pages), so filters are read via $request->query() and
 * re-render the whole page — see PartnerFinanceControllerStatsTest for why
 * the controller is hit directly instead of the HTTP route.
 */
class InvoicesFilterPanelTest extends TestCase
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
        $liveVideo = LiveVideo::create(['title' => 'Auction']);
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

    private function invoicesFor(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        // Binding into the container matters here because the view reads
        // filter values via the global request() helper (correct for real
        // HTTP requests, where Laravel's kernel binds this automatically) —
        // see OrdersColumnsActionsAndTrendTest::rowFor() for the same
        // pattern applied to get_data()-style tests.
        $request = new Request($params);
        app()->instance('request', $request);

        $view = (new PartnerFinanceController())->invoices($request);

        return $view->getData()['invoices'];
    }

    public function test_filter_status_matches_exact_status()
    {
        $pending = $this->createOrder(['status' => 'pending']);
        $shipping = $this->createOrder(['status' => 'shipping']);

        $invoices = $this->invoicesFor(['filter_status' => 'shipping']);

        $this->assertFalse($invoices->contains('id', $pending->id));
        $this->assertTrue($invoices->contains('id', $shipping->id));
    }

    public function test_filter_payment_status_matches_exact_payment_status()
    {
        $paid = $this->createOrder(['payment_status' => 'paid']);
        $unpaid = $this->createOrder(['payment_status' => 'unpaid']);

        $invoices = $this->invoicesFor(['filter_payment_status' => 'paid']);

        $this->assertTrue($invoices->contains('id', $paid->id));
        $this->assertFalse($invoices->contains('id', $unpaid->id));
    }

    /**
     * Dates are deliberately far in the past (~2 years) rather than "a few
     * days ago" — the "shared testing database" this suite runs against
     * (see PartnerFinanceControllerStatsTest's class docblock) is a real,
     * busy dev DB, and this controller's ->paginate(15) truncates results
     * to the first page; a narrow recent-date range risks silently pushing
     * this test's own record past page 1 behind unrelated real rows.
     */
    public function test_filter_date_range_excludes_invoices_outside_the_range()
    {
        $inRange = $this->createOrder();
        $inRange->created_at = now()->subYears(2)->subDays(5);
        $inRange->save();

        $outOfRange = $this->createOrder();
        $outOfRange->created_at = now()->subYears(2)->subDays(30);
        $outOfRange->save();

        $invoices = $this->invoicesFor([
            'filter_date_from' => now()->subYears(2)->subDays(10)->toDateString(),
            'filter_date_to' => now()->subYears(2)->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($invoices->contains('id', $inRange->id));
        $this->assertFalse($invoices->contains('id', $outOfRange->id));
    }

    public function test_status_filter_combines_with_the_existing_search_filter()
    {
        $suffix = random_int(100000, 999999);
        $target = $this->createOrder(['order_number' => "ORD-COMBO-{$suffix}-1", 'status' => 'pending']);
        $wrongStatus = $this->createOrder(['order_number' => "ORD-COMBO-{$suffix}-2", 'status' => 'shipping']);
        $wrongNumber = $this->createOrder(['order_number' => "ORD-NOPE-{$suffix}-1", 'status' => 'pending']);

        $invoices = $this->invoicesFor([
            'search' => "ORD-COMBO-{$suffix}",
            'filter_status' => 'pending',
        ]);

        $this->assertTrue($invoices->contains('id', $target->id));
        $this->assertFalse($invoices->contains('id', $wrongStatus->id));
        $this->assertFalse($invoices->contains('id', $wrongNumber->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $order = $this->createOrder();

        $invoices = $this->invoicesFor([]);

        $this->assertTrue($invoices->contains('id', $order->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_invoices_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->invoices(new Request())->render();

        $this->assertStringContainsString('id="invoicesFilterPanel"', $html);
        $this->assertStringContainsString('name="filter_status"', $html);
        $this->assertStringContainsString('name="filter_payment_status"', $html);
        $this->assertStringContainsString('name="filter_date_from"', $html);
        $this->assertStringContainsString('name="filter_date_to"', $html);
        $this->assertStringContainsString(route('admin.partner-finance.invoices'), $html);
        $this->assertStringContainsString('md-wide-search', $html);
    }

    /**
     * The status <select> must re-mark the previously chosen option as
     * selected after a GET reload — a Laravel-8 project, so this must use a
     * plain ternary, NOT the @selected directive (added in Laravel 9.32;
     * on 8.x it silently renders as literal, uncompiled text, breaking the
     * selection with no error — see docs/dashboard-home-redesign-ar.md for
     * the same bug found on the dashboard home page's day-range filter).
     */
    public function test_status_filter_select_keeps_the_chosen_option_selected_after_reload()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $request = new Request(['filter_status' => 'shipping']);
        app()->instance('request', $request);
        $html = (new PartnerFinanceController())->invoices($request)->render();

        $this->assertStringNotContainsString('@selected', $html);
        $this->assertMatchesRegularExpression(
            '/<option value="shipping" selected>/',
            $html
        );
    }
}
