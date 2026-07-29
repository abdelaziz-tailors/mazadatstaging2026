<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\VendorController;
use App\Http\Requests\Dashboard\Vendor\UpdateVendorRequest;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Bug: /admin/vendors never showed vendors who self-registered from the
 * mobile app. Root cause: VendorController::index()/get_data() always
 * filtered `where('admin_id', <the currently logged-in admin's own id>)`
 * unconditionally — for ANY admin, including the main super-admin. Mobile
 * self-registration (RegisterController) never sets admin_id at all, so
 * those vendors have admin_id = null and were silently excluded no matter
 * who was logged in; vendors created by a *different* partner were excluded
 * too, even from the super-admin's own view.
 *
 * Fixed by only applying that scoping for a partner admin
 * (PartnerDashboardScope::scopeVendors()/ensureOwnVendor()) — matching the
 * scoping pattern already used everywhere else in this dashboard
 * (categories, item-services, live videos, orders, etc.). The main
 * super-admin now sees and can manage every vendor unscoped; a partner
 * admin's own view is unchanged (still their own vendors only).
 */
class VendorAdminIdScopingTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Super Admin',
            'email' => 'super' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    private function createPartnerAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
    }

    private function createVendor(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Vendor',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ], $overrides));
    }

    private function getDataIds(Admin $admin): \Illuminate\Support\Collection
    {
        Auth::guard('admin')->setUser($admin);

        $request = Request::create('/admin/vendors/getData', 'POST', [
            'draw' => 1, 'start' => 0, 'length' => 50,
        ]);
        app()->instance('request', $request);

        $response = (new VendorController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    public function test_super_admin_sees_self_registered_vendors_with_no_admin_id_in_the_table()
    {
        $superAdmin = $this->createSuperAdmin();
        $selfRegistered = $this->createVendor(['name' => 'App Registered Vendor', 'admin_id' => null]);

        $ids = $this->getDataIds($superAdmin);

        $this->assertTrue($ids->contains($selfRegistered->id));
    }

    public function test_super_admin_sees_vendors_created_by_any_partner_in_the_table()
    {
        $superAdmin = $this->createSuperAdmin();
        $partnerA = $this->createPartnerAdmin();
        $partnerB = $this->createPartnerAdmin();

        $vendorA = $this->createVendor(['admin_id' => $partnerA->id]);
        $vendorB = $this->createVendor(['admin_id' => $partnerB->id]);

        $ids = $this->getDataIds($superAdmin);

        $this->assertTrue($ids->contains($vendorA->id));
        $this->assertTrue($ids->contains($vendorB->id));
    }

    public function test_super_admin_index_stats_count_self_registered_and_other_partners_vendors()
    {
        $superAdmin = $this->createSuperAdmin();
        $partner = $this->createPartnerAdmin();

        $this->createVendor(['admin_id' => null]);
        $this->createVendor(['admin_id' => $partner->id]);

        Auth::guard('admin')->setUser($superAdmin);
        $view = (new VendorController())->index(new Request());
        $stats = $view->getData()['stats'];

        $this->assertEquals(2, $stats['total']);
    }

    public function test_partner_admin_still_only_sees_their_own_vendors()
    {
        $partnerA = $this->createPartnerAdmin();
        $partnerB = $this->createPartnerAdmin();

        $ownVendor = $this->createVendor(['admin_id' => $partnerA->id]);
        $othersVendor = $this->createVendor(['admin_id' => $partnerB->id]);
        $selfRegistered = $this->createVendor(['admin_id' => null]);

        $ids = $this->getDataIds($partnerA);

        $this->assertTrue($ids->contains($ownVendor->id));
        $this->assertFalse($ids->contains($othersVendor->id));
        $this->assertFalse($ids->contains($selfRegistered->id));
    }

    public function test_super_admin_can_open_the_edit_page_for_a_self_registered_vendor()
    {
        $superAdmin = $this->createSuperAdmin();
        $vendor = $this->createVendor(['admin_id' => null]);

        Auth::guard('admin')->setUser($superAdmin);
        view()->share('errors', new ViewErrorBag());

        $html = (new VendorController())->edit($vendor->id)->render();

        $this->assertStringContainsString($vendor->name, $html);
    }

    public function test_super_admin_can_open_the_edit_page_for_another_partners_vendor()
    {
        $superAdmin = $this->createSuperAdmin();
        $partner = $this->createPartnerAdmin();
        $vendor = $this->createVendor(['admin_id' => $partner->id]);

        Auth::guard('admin')->setUser($superAdmin);
        view()->share('errors', new ViewErrorBag());

        $html = (new VendorController())->edit($vendor->id)->render();

        $this->assertStringContainsString($vendor->name, $html);
    }

    public function test_super_admin_can_update_a_self_registered_vendor()
    {
        $superAdmin = $this->createSuperAdmin();
        $vendor = $this->createVendor(['admin_id' => null]);
        Auth::guard('admin')->setUser($superAdmin);

        $request = new UpdateVendorRequest();
        $request->merge([
            'name' => 'Updated Name',
            'email' => 'updated' . random_int(100000, 999999) . '@example.com',
            'phone' => $vendor->phone,
        ]);

        (new VendorController())->update($request, $vendor->id);

        $this->assertEquals('Updated Name', $vendor->fresh()->name);
    }

    public function test_partner_admin_still_gets_403_editing_a_vendor_that_is_not_theirs()
    {
        $partner = $this->createPartnerAdmin();
        $otherPartner = $this->createPartnerAdmin();
        $vendor = $this->createVendor(['admin_id' => $otherPartner->id]);

        Auth::guard('admin')->setUser($partner);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new VendorController())->edit($vendor->id);
    }

    public function test_partner_admin_still_gets_403_editing_a_self_registered_vendor()
    {
        $partner = $this->createPartnerAdmin();
        $vendor = $this->createVendor(['admin_id' => null]);

        Auth::guard('admin')->setUser($partner);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new VendorController())->edit($vendor->id);
    }
}
