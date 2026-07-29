<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PackageController;
use App\Http\Requests\Dashboard\Package\UpdatePackageRequest;
use App\Models\Admin;
use App\Models\Package;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: the "Subscription Type" (monthly/annual) field was fully
 * wired end-to-end — DB column, validation rules in Store/UpdatePackageRequest,
 * and both store()/update() already read $request->subscription_type — but
 * the actual <select> in the shared _form.blade.php partial (used by both
 * the create and edit pages) was commented out, so there was no way for an
 * admin to ever set it. That's why every real package in production shows
 * "-" for this column: not a display bug, a missing input field. Restored
 * the field; these tests guard against it being silently commented out
 * again.
 */
class PackageSubscriptionTypeFieldTest extends TestCase
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

        foreach (['add package', 'edit package'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

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

    public function test_create_form_has_a_subscription_type_select_field()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new PackageController())->create()->render();

        $this->assertStringContainsString('name="subscription_type"', $html);
        $this->assertStringContainsString(TranslationHelper::translate('Monthly'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('Annual'), $html);
    }

    public function test_edit_form_has_a_subscription_type_select_field_preselecting_the_current_value()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $package = $this->createPackage(['subscription_type' => 'monthly']);

        $html = (new PackageController())->edit($package->id)->render();

        $this->assertStringContainsString('name="subscription_type"', $html);
        $this->assertMatchesRegularExpression(
            '/<option value="monthly" selected="selected">/',
            $html
        );
    }

    public function test_updating_a_package_with_a_subscription_type_persists_it()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $package = $this->createPackage(['subscription_type' => null]);

        $request = new UpdatePackageRequest();
        $request->merge([
            'name' => ['ar' => 'باقة محدثة', 'en' => 'Updated Package'],
            'description' => ['ar' => 'وصف', 'en' => 'Description'],
            'features' => ['ar' => [], 'en' => []],
            'subscription_type' => 'annual',
            'auctions_limit' => 20,
            'monthly_price' => 100,
            'annual_price' => 1000,
        ]);

        (new PackageController())->update($request, $package->id);

        $this->assertEquals('annual', $package->fresh()->subscription_type);
    }
}
