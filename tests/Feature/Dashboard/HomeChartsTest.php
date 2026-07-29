<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Regression guard for a small-screen-only bug: the auctions-by-status donut
 * chart had no stroke between slices ("stroke: { width: 0 }"), which made
 * adjacent slices visually blend into each other once the chart was
 * squeezed onto a narrow mobile width. Large screens rendered fine.
 *
 * The chart now shows counts via an external per-status legend and a
 * center-of-ring total (see .md-status-donut-row in the blade template)
 * instead of in-slice percentage labels, so dataLabels are unconditionally
 * disabled and there's no longer a mobile-only breakpoint override needed —
 * the CSS handles stacking the legend above the ring on narrow screens.
 */
class HomeChartsTest extends TestCase
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

    public function test_home_page_renders_the_status_donut_chart_with_a_slice_stroke_and_no_in_ring_labels()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        // The donut chart only mounts when there's at least one auction in
        // the selected date range (an all-zero series can't be rendered as
        // a donut — see DashboardHomeStatsTest's regression guard for that).
        LiveVideo::create(['title' => 'Auction', 'status' => 'start']);

        $view = (new DashboardController())->index();
        $html = $view->render();

        $this->assertStringContainsString('statusDonutChart', $html);
        $this->assertStringContainsString("stroke: { show: true, width: 2, colors: ['#ffffff'] }", $html);
        $this->assertStringContainsString('dataLabels: { enabled: false }', $html);
        // The count-per-status legend and the center-of-ring total, which
        // replace the old in-slice percentage labels.
        $this->assertStringContainsString('md-status-donut-total-value', $html);
        $this->assertStringContainsString('md-status-legend-count', $html);
    }

    /**
     * Regression guard: the home page's "المستخدمين" (Users) stat card used
     * to exclude vendors ("user_type != vendor"), while the Users page's own
     * "إجمالي المستخدمين" total counts everyone — same label, two different
     * numbers on two different screens. Made them match: both now count all
     * real account types.
     */
    public function test_users_report_card_matches_the_true_total_user_count()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        User::create([
            'name' => 'Vendor Consistency Check', 'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'), 'user_type' => 'vendor', 'gender' => 'male',
        ]);

        $expectedTotal = User::count();

        $view = (new DashboardController())->index();
        $reports = collect($view->getData()['reports']);
        $usersReport = $reports->firstWhere('name', 'Users');

        $this->assertNotNull($usersReport);
        $this->assertEquals($expectedTotal, $usersReport['value']);
    }

    /**
     * Regression guard: the registrations/sales trend charts show 30 daily
     * labels, and ApexCharts' own "tickAmount" wasn't reliably thinning them
     * out on a category x-axis — every date rendered and crowded together
     * until they were unreadable. A deterministic label formatter (blank out
     * every label except every Nth one) now guarantees real spacing
     * regardless of that.
     */
    public function test_trend_charts_thin_out_x_axis_labels_with_a_deterministic_formatter()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('const step = Math.max(1, Math.ceil(categories.length / desiredTicks));', $html);
        $this->assertStringContainsString("idx % step === 0", $html);
    }

    /**
     * Regression guard: the "تسجيلات المستخدمين" (user registrations) trend
     * chart's Y-axis showed fractional tick values (0.7, 1.3, 2.0) whenever
     * the max count in range was small — ApexCharts' default "nice scale"
     * tick generator doesn't know a user count can only ever be a whole
     * number. A first attempt used yaxis.decimalsInFloat: 0, which only
     * formats the digits ApexCharts decides to draw — it doesn't stop the
     * generator from choosing fractional tick positions in the first place,
     * so it didn't actually fix anything (confirmed against the live app).
     * Replaced with an explicit min/max/tickAmount computed from the real
     * data so every generated tick is guaranteed to land on a whole number.
     */
    public function test_registrations_chart_y_axis_uses_a_whole_number_tick_amount_and_max()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $registrationsChartPos = strpos($html, 'querySelector("#registrationsChart")');
        $this->assertNotFalse($registrationsChartPos);

        $chartBlock = substr($html, max(0, $registrationsChartPos - 700), 1900);
        $this->assertStringContainsString('var rawMax = Math.max(1, ...data);', $chartBlock);
        $this->assertStringContainsString('var tickAmount = Math.min(rawMax, 5);', $chartBlock);
        $this->assertStringContainsString('var niceMax = Math.ceil(rawMax / tickAmount) * tickAmount;', $chartBlock);
        $this->assertStringContainsString('min: 0, max: niceMax, tickAmount: tickAmount', $chartBlock);
        $this->assertStringNotContainsString('decimalsInFloat', $chartBlock);
    }
}
