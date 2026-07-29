<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\VendorController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The vendors list (/admin/vendors) had a broken layout (a hardcoded
 * "position: absolute" CSS hack on the export buttons made them float above
 * the page title instead of sitting in the toolbar), untranslated export
 * button labels ("Export as PDF"/"Export as Excel" hardcoded in English),
 * no summary "brief" stat cards, no descriptive search placeholder, and its
 * row actions used raw colored icons with no "view" page at all. This adds
 * a real read-only show page, restyles the actions to the round icon-button
 * component (view/edit/kebab-delete), fixes the layout/translation bugs,
 * and adds real stats with month-over-month trends — all scoped to the
 * current admin's own vendors, matching get_data()'s existing scoping.
 */
class VendorsStatsActionsAndSearchTest extends TestCase
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

    private function createPartnerAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Partner',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
    }

    private function createVendor(Admin $admin, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Vendor',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
            'admin_id' => $admin->id,
        ], $overrides));
    }

    public function test_index_computes_real_stats_scoped_to_the_current_admin()
    {
        $admin = $this->createPartnerAdmin();
        $otherAdmin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);

        $this->createVendor($admin, ['is_active' => true]);
        $this->createVendor($admin, ['is_active' => false]);
        $this->createVendor($admin, ['is_active' => false]);
        // A vendor belonging to a different admin must not be counted.
        $this->createVendor($otherAdmin, ['is_active' => true]);

        $view = (new VendorController())->index(new Request());
        $stats = $view->getData()['stats'];

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
    }

    public function test_index_page_renders_the_stat_cards_with_trends_and_a_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VendorController())->index(new Request())->render();

        $this->assertStringContainsString(TranslationHelper::translate('new_this_month'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('active_vendors'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('inactive_vendors'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('total_vendors'), $html);
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'stat-trend'));
        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_vendors_placeholder'), $html);
    }

    public function test_export_buttons_are_translated_not_hardcoded_english()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VendorController())->index(new Request())->render();

        // Wired through TranslationHelper (correctly reads as English text
        // in the English locale — that's expected, not the bug). The old
        // bug was the malformed hardcoded literals below, which must be gone.
        $this->assertStringContainsString(TranslationHelper::translate('export_as_excel_'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('export_as_pdf_'), $html);
        $this->assertStringNotContainsString('Export as Excel  ', $html);
        $this->assertStringNotContainsString('Export as  PDF', $html);
    }

    public function test_export_buttons_no_longer_use_the_broken_absolute_position_hack()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VendorController())->index(new Request())->render();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('margin-top: -166px', $html);
    }

    /**
     * Delete was pulled out of the kebab dropdown into its own standalone
     * red icon — matching the roles/users pages pattern — per explicit
     * request, so there's no dropdown menu here anymore at all.
     */
    public function test_actions_partial_has_view_edit_and_standalone_delete_icons()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $vendor = $this->createVendor($this->createAdmin());

        $html = view('dashboard.pages.vendors.actions', ['item' => $vendor])->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString('md-icon-btn-danger', $html);
        $this->assertStringContainsString('fa-trash', $html);
        $this->assertStringContainsString(route('admin.vendors.show', $vendor->id), $html);
        $this->assertStringContainsString(route('admin.vendors.edit', $vendor->id), $html);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
        $this->assertStringNotContainsString('btn-group', $html);
        $this->assertStringNotContainsString('style="color: #2196f3"', $html);
        $this->assertStringNotContainsString('style="color: #e42f2f"', $html);
    }

    public function test_datatable_toolbar_and_pagination_wrappers_have_horizontal_padding()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VendorController())->index(new Request())->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
        $this->assertStringContainsString('d-flex justify-content-between px-2', $html);
    }

    public function test_show_page_renders_the_vendors_details()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $vendor = $this->createVendor($this->createAdmin(), [
            'name' => 'Jane Vendor',
            'email' => 'jane-vendor@example.com',
            'national_id' => '1010257004',
        ]);

        $html = (new VendorController())->show($vendor->id)->render();

        $this->assertStringContainsString('Jane Vendor', $html);
        $this->assertStringContainsString('jane-vendor@example.com', $html);
        $this->assertStringContainsString('1010257004', $html);
    }
}
