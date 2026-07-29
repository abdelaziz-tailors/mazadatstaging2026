<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\VendorController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Covers the vendors-page filter panel added on top of VendorController::get_data():
 * username, email, status, and date range filters — the same pattern already
 * built for the buyers page (UserBuyersFilterPanelTest), built from real,
 * already-stored columns (name/email/is_active/created_at), no new schema.
 * get_data() only scopes vendors to the current admin's own admin_id for a
 * *partner* admin (PartnerDashboardScope::scopeVendors()) — the main
 * super-admin sees every vendor unscoped, including self-registered ones
 * with no admin_id at all (see VendorAdminIdScopingTest). The one test here
 * that exercises cross-admin scoping (test_filters_stay_scoped_to_the_current_admins_own_vendors)
 * uses a partner admin accordingly; every other test only ever creates
 * vendors under a single admin, so it's unaffected either way.
 */
class VendorsFilterPanelTest extends TestCase
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
            'name' => 'Vendor ' . random_int(100000, 999999),
            'email' => 'vendor' . random_int(100000, 999999) . '@example.com',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
            'is_active' => true,
            'admin_id' => $admin->id,
        ], $overrides));
    }

    private function callGetData(Admin $admin, array $params)
    {
        Auth::guard('admin')->setUser($admin);

        $request = Request::create('/admin/vendors/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $params));
        app()->instance('request', $request);

        $response = (new VendorController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    public function test_filter_username_matches_by_partial_name()
    {
        $admin = $this->createAdmin();
        $match = $this->createVendor($admin, ['name' => 'Ahmed Special Vendor']);
        $other = $this->createVendor($admin, ['name' => 'Someone Else']);

        $ids = $this->callGetData($admin, ['filter_username' => 'Special Vendor']);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_email_matches_by_partial_email()
    {
        $admin = $this->createAdmin();
        $match = $this->createVendor($admin, ['email' => 'unique.match@example.com']);
        $other = $this->createVendor($admin, ['email' => 'nomatch@example.com']);

        $ids = $this->callGetData($admin, ['filter_email' => 'unique.match']);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_status_active_excludes_inactive_vendors()
    {
        $admin = $this->createAdmin();
        $active = $this->createVendor($admin, ['is_active' => true]);
        $inactive = $this->createVendor($admin, ['is_active' => false]);

        $ids = $this->callGetData($admin, ['filter_status' => '1']);

        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_filter_status_inactive_excludes_active_vendors()
    {
        $admin = $this->createAdmin();
        $active = $this->createVendor($admin, ['is_active' => true]);
        $inactive = $this->createVendor($admin, ['is_active' => false]);

        $ids = $this->callGetData($admin, ['filter_status' => '0']);

        $this->assertFalse($ids->contains($active->id));
        $this->assertTrue($ids->contains($inactive->id));
    }

    public function test_filter_date_range_excludes_vendors_outside_the_range()
    {
        $admin = $this->createAdmin();

        $inRange = $this->createVendor($admin);
        $inRange->created_at = now()->subDays(5);
        $inRange->save();

        $outOfRange = $this->createVendor($admin);
        $outOfRange->created_at = now()->subDays(30);
        $outOfRange->save();

        $ids = $this->callGetData($admin, [
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_combined_filters_apply_together_as_an_intersection()
    {
        $admin = $this->createAdmin();
        $match = $this->createVendor($admin, ['name' => 'Combo Match', 'is_active' => true]);
        $wrongName = $this->createVendor($admin, ['name' => 'Nope', 'is_active' => true]);
        $wrongStatus = $this->createVendor($admin, ['name' => 'Combo Match', 'is_active' => false]);

        $ids = $this->callGetData($admin, [
            'filter_username' => 'Combo Match',
            'filter_status' => '1',
        ]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongName->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $admin = $this->createAdmin();
        $vendor = $this->createVendor($admin);

        $ids = $this->callGetData($admin, []);

        $this->assertTrue($ids->contains($vendor->id));
    }

    /**
     * The filters must stay scoped to the current admin's own vendors even
     * when a filter value would otherwise match a vendor belonging to a
     * different admin — get_data() scopes by admin_id first, filters apply
     * on top of that, not instead of it.
     */
    public function test_filters_stay_scoped_to_the_current_admins_own_vendors()
    {
        $adminA = $this->createPartnerAdmin();
        $adminB = $this->createAdmin();

        $ownVendor = $this->createVendor($adminA, ['name' => 'Shared Name Vendor']);
        $otherAdminsVendor = $this->createVendor($adminB, ['name' => 'Shared Name Vendor']);

        $ids = $this->callGetData($adminA, ['filter_username' => 'Shared Name Vendor']);

        $this->assertTrue($ids->contains($ownVendor->id));
        $this->assertFalse($ids->contains($otherAdminsVendor->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_vendors_index_page()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $view = (new VendorController())->index(new Request());
        $html = $view->render();

        $this->assertStringContainsString('id="vendorsFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_username"', $html);
        $this->assertStringContainsString('id="filter_email"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
    }
}
