<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Covers two explicit fixes to the partner/subscriber sidebar
 * (resources/views/dashboard/layouts/sidebar_subscriber.blade.php, rendered
 * for Admin.type === 'partner' — see dashboard.layouts.app's @if check):
 *
 * 1. The "لوحة التحكم" (Dashboard/home) link had gone missing entirely —
 *    every other section of this sidebar links somewhere, but there was no
 *    way back to the dashboard home itself. Added at the very top, matching
 *    the main sidebar's own dashboard link (fa-solid fa-house icon,
 *    route('admin.dashboard.index')).
 *
 * 2. "خدمات القطعة" (Item Services) moved to sit right after the
 *    "الفواتير" (Invoices) section, instead of appearing right after
 *    "طلبات عرض القطع" (piece offer requests / seller-submissions).
 */
class SubscriberSidebarTest extends TestCase
{
    use DatabaseTransactions;

    private function createPartnerAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
    }

    /**
     * Scoped to just the <aside id="sidebar-menu">...</aside> markup — the
     * dashboard link also appears elsewhere on the page (e.g. breadcrumbs),
     * so comparing raw string positions across the whole page would be
     * unreliable.
     */
    private function renderSidebar(): string
    {
        Auth::guard('admin')->setUser($this->createPartnerAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $start = strpos($html, 'id="sidebar-menu"');
        $end = strpos($html, 'page-wrapper');

        return substr($html, $start, $end - $start);
    }

    public function test_dashboard_link_is_present_in_the_subscriber_sidebar()
    {
        $html = $this->renderSidebar();

        $this->assertStringContainsString(route('admin.dashboard.index'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('dashboard'), $html);
        $this->assertStringContainsString('fa-solid fa-house', $html);
    }

    public function test_dashboard_link_is_the_first_item_under_the_main_menu_title()
    {
        $html = $this->renderSidebar();

        $menuTitlePos = strpos($html, TranslationHelper::translate('subscriber_main_menu'));
        $dashboardPos = strpos($html, route('admin.dashboard.index'));
        $newAuctionPos = strpos($html, route('admin.videos.create'));

        $this->assertNotFalse($menuTitlePos);
        $this->assertNotFalse($dashboardPos);
        $this->assertNotFalse($newAuctionPos);
        $this->assertGreaterThan($menuTitlePos, $dashboardPos);
        $this->assertLessThan($newAuctionPos, $dashboardPos);
    }

    public function test_item_services_link_now_sits_right_after_invoices()
    {
        $html = $this->renderSidebar();

        $invoicesPos = strpos($html, route('admin.partner-finance.invoices'));
        $itemServicesPos = strpos($html, route('admin.item-services.index'));
        $walletPos = strpos($html, route('admin.partner-finance.wallet'));
        $pieceOfferRequestsPos = strpos($html, route('admin.seller-submissions.index'));

        $this->assertNotFalse($invoicesPos);
        $this->assertNotFalse($itemServicesPos);
        $this->assertNotFalse($walletPos);

        // Invoices comes after piece-offer-requests (unchanged ordering)...
        $this->assertGreaterThan($pieceOfferRequestsPos, $invoicesPos);
        // ...and item services no longer sits right after piece-offer-requests;
        // it now sits between invoices and wallet instead.
        $this->assertGreaterThan($invoicesPos, $itemServicesPos);
        $this->assertLessThan($walletPos, $itemServicesPos);
    }

    public function test_super_admin_still_sees_the_main_sidebar_not_the_subscriber_one()
    {
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        Auth::guard('admin')->setUser($superAdmin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringNotContainsString(TranslationHelper::translate('subscriber_main_menu'), $html);
    }
}
