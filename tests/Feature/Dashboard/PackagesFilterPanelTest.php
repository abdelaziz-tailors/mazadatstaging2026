<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\PackageController;
use App\Models\Admin;
use App\Models\Package;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the packages-page filter panel added on top of
 * PackageController::get_data(): subscription type, status, and date range
 * filters — the same pattern already built for buyers/vendors/auctions/
 * orders/partners, built from real, already-stored columns
 * (subscription_type/is_active/created_at), no new schema.
 *
 * get_data() builds a Package query with a select() then ->get() (a
 * Collection, not a query builder, is handed to Datatables::of()) — filters
 * must apply to the query BEFORE ->get() is called, or they would silently
 * no-op against an already-fetched, unfiltered collection.
 */
class PackagesFilterPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        $permission = Permission::firstOrCreate(['name' => 'view packages', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    private function createPackage(array $overrides = []): Package
    {
        return Package::create(array_merge([
            'name' => json_encode(['ar' => 'باقة تجريبية', 'en' => 'Test Package']),
            'description' => json_encode(['ar' => 'وصف الباقة', 'en' => 'Package description']),
            'features' => json_encode(['ar' => [], 'en' => []]),
            'coin' => 0,
            'price' => 0,
            'is_active' => true,
        ], $overrides));
    }

    private function callGetData(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $request = Request::create('/admin/packages/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $params));
        app()->instance('request', $request);

        $response = (new PackageController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    public function test_filter_subscription_type_monthly_excludes_annual_packages()
    {
        $monthly = $this->createPackage(['subscription_type' => 'monthly']);
        $annual = $this->createPackage(['subscription_type' => 'annual']);

        $ids = $this->callGetData(['filter_subscription_type' => 'monthly']);

        $this->assertTrue($ids->contains($monthly->id));
        $this->assertFalse($ids->contains($annual->id));
    }

    public function test_filter_subscription_type_annual_excludes_monthly_packages()
    {
        $monthly = $this->createPackage(['subscription_type' => 'monthly']);
        $annual = $this->createPackage(['subscription_type' => 'annual']);

        $ids = $this->callGetData(['filter_subscription_type' => 'annual']);

        $this->assertFalse($ids->contains($monthly->id));
        $this->assertTrue($ids->contains($annual->id));
    }

    public function test_filter_status_active_excludes_inactive_packages()
    {
        $active = $this->createPackage(['is_active' => true]);
        $inactive = $this->createPackage(['is_active' => false]);

        $ids = $this->callGetData(['filter_status' => '1']);

        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_filter_status_inactive_excludes_active_packages()
    {
        $active = $this->createPackage(['is_active' => true]);
        $inactive = $this->createPackage(['is_active' => false]);

        $ids = $this->callGetData(['filter_status' => '0']);

        $this->assertFalse($ids->contains($active->id));
        $this->assertTrue($ids->contains($inactive->id));
    }

    public function test_filter_date_range_excludes_packages_outside_the_range()
    {
        $inRange = $this->createPackage();
        $inRange->created_at = now()->subDays(5);
        $inRange->save();

        $outOfRange = $this->createPackage();
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
        $match = $this->createPackage(['subscription_type' => 'monthly', 'is_active' => true]);
        $wrongType = $this->createPackage(['subscription_type' => 'annual', 'is_active' => true]);
        $wrongStatus = $this->createPackage(['subscription_type' => 'monthly', 'is_active' => false]);

        $ids = $this->callGetData([
            'filter_subscription_type' => 'monthly',
            'filter_status' => '1',
        ]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongType->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $package = $this->createPackage();

        $ids = $this->callGetData([]);

        $this->assertTrue($ids->contains($package->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_packages_index_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PackageController())->index()->render();

        $this->assertStringContainsString('id="packagesFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_subscription_type"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
        $this->assertStringContainsString('md-wide-search', $html);
    }
}
