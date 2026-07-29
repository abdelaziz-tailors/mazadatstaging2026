<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\SellerSubmission;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The super-admin dashboard home page (/admin) was rebuilt to match a
 * design reference: 8 real stat cards (each with a real month-over-month
 * trend %), a status-breakdown donut + two trend charts sharing a
 * "last N days" filter, latest-users/latest-auctions panels, and a quick
 * actions grid. Every number here is a live DB computation — nothing
 * hardcoded or cached.
 */
class DashboardHomeStatsTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
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
            'name' => 'Buyer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    public function test_sales_total_and_trend_are_real_and_match_a_direct_query()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());

        $baselineTotal = (float) LiveVideoItem::whereNotNull('finished_price')->sum('finished_price');
        $baselineThisMonth = (float) LiveVideoItem::whereNotNull('finished_price')
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)
            ->sum('finished_price');

        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 500]);

        $view = (new DashboardController())->index(new Request());
        $sales = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Sales'));

        $this->assertEquals($baselineTotal + 500, $sales['value']);
        $this->assertEquals($baselineThisMonth + 500 > 0 ? 'up' : 'down', $sales['trend']['direction']) ;
    }

    public function test_users_total_matches_a_direct_user_count()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        $baseline = User::count();

        $this->createBuyer();
        $this->createBuyer();

        $view = (new DashboardController())->index(new Request());
        $users = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Users'));

        $this->assertEquals($baseline + 2, $users['value']);
    }

    /**
     * The "Categories" stat card was removed from the dashboard home page
     * (both the super-admin and the partner view) per explicit request.
     */
    public function test_categories_card_is_not_shown_to_the_super_admin()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());

        $view = (new DashboardController())->index(new Request());
        $categories = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Categories'));

        $this->assertNull($categories);
    }

    public function test_categories_card_is_not_shown_to_a_partner()
    {
        $partner = Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
        Auth::guard('admin')->setUser($partner);

        $view = (new DashboardController())->index(new Request());
        $categories = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Categories'));

        $this->assertNull($categories);
    }

    public function test_auction_products_total_matches_a_direct_live_video_item_count()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        $baseline = LiveVideoItem::count();

        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id]);

        $view = (new DashboardController())->index(new Request());
        $items = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Auctions Product'));

        $this->assertEquals($baseline + 2, $items['value']);
    }

    public function test_active_auctions_total_only_counts_status_start()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        $baseline = LiveVideo::where('status', 'start')->count();

        LiveVideo::create(['title' => 'Live', 'status' => 'start']);
        LiveVideo::create(['title' => 'Ended', 'status' => 'end']);
        LiveVideo::create(['title' => 'Upcoming', 'status' => null]);

        $view = (new DashboardController())->index(new Request());
        $active = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('active_auctions_dashboard'));

        $this->assertEquals($baseline + 1, $active['value']);
    }

    public function test_vendors_total_only_counts_user_type_vendor()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        $baseline = User::where('user_type', 'vendor')->count();

        $this->createBuyer(['user_type' => 'vendor']);
        $this->createBuyer(['user_type' => 'buyer']);

        $view = (new DashboardController())->index(new Request());
        $vendors = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Vendors'));

        $this->assertEquals($baseline + 1, $vendors['value']);
    }

    public function test_orders_total_matches_a_direct_order_count()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        $baseline = Order::count();

        $buyer = $this->createBuyer();
        Order::create([
            'order_number' => 'ORD-DASH-' . random_int(100000, 999999),
            'live_video_id' => LiveVideo::create(['title' => 'Auction 1'])->id,
            'buyer_id' => $buyer->id,
            'total' => 100,
        ]);

        $view = (new DashboardController())->index(new Request());
        $orders = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Orders'));

        $this->assertEquals($baseline + 1, $orders['value']);
    }

    public function test_partners_total_only_counts_admin_type_partner()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        $baseline = Admin::where('type', 'partner')->count();

        Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);

        $view = (new DashboardController())->index(new Request());
        $partners = collect($view->getData()['reports'])->firstWhere('name', TranslationHelper::translate('Partners'));

        $this->assertEquals($baseline + 1, $partners['value']);
    }

    public function test_invalid_days_query_param_falls_back_to_30()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());

        $view = (new DashboardController())->index(new Request(['days' => 999]));

        $this->assertEquals(30, $view->getData()['days']);
    }

    public function test_valid_days_query_param_is_honored()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());

        $view = (new DashboardController())->index(new Request(['days' => 7]));

        $this->assertEquals(7, $view->getData()['days']);
        $this->assertCount(7, $view->getData()['salesChart']['values']);
    }

    /**
     * Regression guard: the "last N days" <select> options used Blade's
     * @selected directive, which does not exist in Laravel 8 (added in
     * Laravel 9.32) — Blade left it completely uncompiled, printing the
     * literal text "@selected($days == $option)" in the HTML instead of
     * a "selected" attribute. The <select> therefore silently always
     * displayed its first option (7 days) no matter which value was
     * actually active, with no visible error anywhere.
     */
    public function test_days_dropdown_marks_the_active_option_as_selected_not_the_literal_directive_text()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index(new Request(['days' => 90]))->render();

        $this->assertStringNotContainsString('@selected', $html);

        // Each <select> should have exactly one "selected" option, and it
        // must be the one whose value URL carries "days=90".
        preg_match_all('/<option[^>]*>/', $html, $optionTags);
        $selectedOptions = array_values(array_filter($optionTags[0], fn ($tag) => str_contains($tag, 'selected')));

        $this->assertNotEmpty($selectedOptions, 'Expected at least one <option> to carry the selected attribute.');
        foreach ($selectedOptions as $tag) {
            $this->assertStringContainsString('days=90', $tag);
        }
    }

    public function test_pending_review_count_sums_seller_submissions_and_pending_subscriptions()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());

        $baselineSubmissions = SellerSubmission::whereNotIn('status', ['approved', 'rejected'])->count();
        $baselineSubscriptions = UserSubscription::where('status', 'pending')->orWhereNull('status')->count();

        $partner = $this->createBuyer(['user_type' => 'vendor']);
        SellerSubmission::create(['sheep_type' => 'Test', 'status' => 'pending', 'partner_id' => $partner->id]);
        SellerSubmission::create(['sheep_type' => 'Test', 'status' => 'approved', 'partner_id' => $partner->id]);

        $buyer = $this->createBuyer();
        UserSubscription::create(['user_id' => $buyer->id, 'status' => 'pending']);

        $view = (new DashboardController())->index(new Request());

        $this->assertEquals($baselineSubmissions + 1 + $baselineSubscriptions + 1, $view->getData()['pendingReviewCount']);
    }

    public function test_index_page_renders_without_errors()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index(new Request())->render();

        $this->assertStringContainsString(TranslationHelper::translate('quick_actions'), $html);
        $this->assertStringContainsString('stat-grid', $html);
        $this->assertGreaterThan(0, strlen($html));
    }

    public function test_partner_admin_still_sees_a_scoped_dashboard_without_errors()
    {
        $partnerUser = $this->createBuyer(['user_type' => 'vendor']);
        $partnerAdmin = Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'user_id' => $partnerUser->id,
        ]);
        Auth::guard('admin')->setUser($partnerAdmin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index(new Request())->render();

        $this->assertGreaterThan(0, strlen($html));
    }

    /**
     * Regression guard: ApexCharts can't compute percentages for an
     * all-zero donut series (division by zero) — it silently rendered as a
     * broken sliver/line instead of a ring. The chart div (and its JS
     * instantiation) must not be emitted at all when there's no data for
     * the selected range; a "no results" message should show instead.
     */
    public function test_status_donut_chart_is_not_rendered_when_all_buckets_are_zero()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = view('dashboard.home', $this->minimalHomeViewData(['active' => 0, 'scheduled' => 0, 'ended' => 0]))->render();

        $this->assertStringNotContainsString('id="statusDonutChart"', $html);
        $this->assertStringNotContainsString('new ApexCharts(document.querySelector("#statusDonutChart")', $html);
        $this->assertStringContainsString(TranslationHelper::translate('no_results_found'), $html);
    }

    public function test_status_donut_chart_is_rendered_when_there_is_real_data()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = view('dashboard.home', $this->minimalHomeViewData(['active' => 0, 'scheduled' => 0, 'ended' => 3]))->render();

        $this->assertStringContainsString('id="statusDonutChart"', $html);
        $this->assertStringContainsString('new ApexCharts(document.querySelector("#statusDonutChart")', $html);
    }

    private function minimalHomeViewData(array $statusChart): array
    {
        return [
            'reports' => [],
            'registrationsChart' => ['labels' => ['1 Jan'], 'values' => [0]],
            'salesChart' => ['labels' => ['1 Jan'], 'values' => [0]],
            'statusChart' => $statusChart,
            'latestUsers' => collect(),
            'latestAuctions' => collect(),
            'registrationsTrend' => ['direction' => 'up', 'pct' => 0.0, 'value' => 0.0],
            'salesTrend' => ['direction' => 'up', 'pct' => 0.0, 'value' => 0.0],
            'days' => 30,
            'pendingReviewCount' => 0,
        ];
    }
}
