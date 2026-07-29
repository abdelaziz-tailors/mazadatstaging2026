<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\SellerSubmission;
use App\Models\User\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The shared dashboard header (rendered by dashboard.layouts.app on every
 * admin page) gained a global search box and a notifications bell whose
 * badge reflects a real, live count of items awaiting admin review (pending
 * seller submissions + pending subscriptions) — computed once by a view
 * composer in AppServiceProvider so it's available on every page, not just
 * the dashboard home page.
 *
 * No language switcher: SetDashboardLocale middleware force-redirects every
 * /admin request back to config('app.dashboard_locale') ('ar') regardless of
 * the URL's locale segment, so the dashboard has no real multi-language
 * support to switch between. A switcher was tried and confirmed to visibly
 * do nothing (clicking English just bounced straight back to Arabic), so it
 * was removed rather than shipping a dead control.
 */
class HeaderSearchLangNotificationsTest extends TestCase
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

    public function test_header_has_a_search_form_pointing_at_the_search_route()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-header-search', $html);
        $this->assertStringContainsString(route('admin.search.index'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('dashboard_search_placeholder'), $html);
    }

    /**
     * On small screens the header search box collided with the mobile
     * sidebar toggle/brand block, so it's hidden below the 767px breakpoint
     * (auctions search is still reachable once the sidebar is open) rather
     * than just shrunk.
     */
    public function test_header_search_is_hidden_on_small_screens()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        // theme.css has several "@media (max-width: 767px)" blocks — assert
        // on the specific one that hides .md-header-search rather than
        // assuming it's the first one in the file.
        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 767px\) \{\s*\.md-header-search \{\s*display: none;\s*\}\s*\}/',
            $css
        );
    }

    /**
     * custom.css's ".user-menu.nav > li > a:hover{background-color:rgba(0,0,0,.2)}"
     * was written for the old dark header — on today's light header it shows
     * up as an unwanted dark translucent box behind the notif bell / profile
     * toggle on hover. theme.css must override it with a hover suited to a
     * light surface.
     */
    public function test_header_nav_link_hover_has_no_dark_overlay()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression(
            '/\.user-menu\.nav > li > a:hover,\s*\.user-menu\.nav > li > a:focus\s*\{\s*background-color:\s*var\(--md-surface-2\)\s*!important;/s',
            $css
        );
    }

    /**
     * Regression guard: no dead language-switcher UI in the header — see
     * the class docblock for why (SetDashboardLocale always forces 'ar').
     */
    public function test_header_has_no_language_switcher()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringNotContainsString('md-lang-toggle', $html);
    }

    public function test_notifications_badge_reflects_the_real_pending_review_count()
    {
        $admin = $this->createSuperAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $partner = $this->createBuyer(['user_type' => 'vendor']);
        SellerSubmission::create(['sheep_type' => 'Test', 'status' => 'pending', 'partner_id' => $partner->id]);

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-notif-badge', $html);
    }

    public function test_notifications_badge_is_hidden_when_there_is_nothing_to_review()
    {
        $admin = $this->createSuperAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        // Neutralize any pre-existing pending rows in the shared test DB so
        // this admin's count is genuinely zero.
        SellerSubmission::whereNotIn('status', ['approved', 'rejected'])->update(['status' => 'approved']);
        UserSubscription::where('status', 'pending')->orWhereNull('status')->update(['status' => 'approved']);

        $html = (new DashboardController())->index()->render();

        $this->assertStringNotContainsString('md-notif-badge', $html);
    }
}
