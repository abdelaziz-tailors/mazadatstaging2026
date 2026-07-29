<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PackageController;
use App\Models\Admin;
use App\Models\Package;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The packages list (/admin/packages) had no summary "brief" stat cards
 * (unlike Auctions/Users), its row actions used the old colored dropdown
 * style instead of the round icon-button component, and its search box had
 * no descriptive placeholder. This adds all three, matching the pattern
 * already established on Auctions/Users/Partners.
 */
class PackagesStatsActionsAndSearchTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(array $permissions = ['view packages', 'edit package', 'delete package']): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach ($permissions as $name) {
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

    public function test_index_computes_real_stats_from_the_database()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $baseline = Package::query()->selectRaw("
            count(*) as total,
            sum(is_active = 1) as active,
            avg(monthly_price) as avg_price,
            max(auctions_limit) as max_limit
        ")->first();

        $this->createPackage(['monthly_price' => 500, 'auctions_limit' => 999, 'is_active' => true]);
        $this->createPackage(['monthly_price' => 100, 'auctions_limit' => 10, 'is_active' => false]);

        $view = (new PackageController())->index();
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->total + 2, $stats['total']);
        $this->assertEquals((int) $baseline->active + 1, $stats['active']);
        $this->assertEquals(999, $stats['max_auctions_limit']);
        $this->assertGreaterThan(0, $stats['avg_monthly_price']);
    }

    public function test_index_page_renders_the_stat_cards_and_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new PackageController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('total_packages'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('active_packages'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('avg_monthly_price'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('max_auctions_limit'), $html);
        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_packages_placeholder'), $html);
    }

    /**
     * Delete was pulled out of the kebab dropdown into its own standalone
     * red icon — matching the buyers/roles pages pattern — per explicit
     * request, so there's no dropdown menu here anymore at all.
     */
    public function test_actions_are_round_icon_buttons_view_edit_and_standalone_delete()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $package = $this->createPackage();

        $html = view('dashboard.pages.packages.actions', ['item' => $package])->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString('md-icon-btn-danger', $html);
        $this->assertStringContainsString('fa-trash', $html);
        $this->assertStringContainsString(route('admin.packages.show', $package->id), $html);
        $this->assertStringContainsString(route('admin.packages.edit', $package->id), $html);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
        $this->assertStringNotContainsString('btn-group', $html);
    }

    public function test_delete_icon_is_hidden_without_delete_package_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view packages', 'edit package']));
        view()->share('errors', new ViewErrorBag());

        $package = $this->createPackage();

        $html = view('dashboard.pages.packages.actions', ['item' => $package])->render();

        $this->assertStringNotContainsString('md-icon-btn-danger', $html);
    }

    public function test_show_page_renders_the_packages_details()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $package = $this->createPackage([
            'subscription_type' => 'monthly',
            'auctions_limit' => 50,
            'monthly_price' => 300,
            'annual_price' => 3000,
        ]);

        $html = (new PackageController())->show($package->id)->render();

        $this->assertStringContainsString('باقة تجريبية', $html);
        $this->assertStringContainsString(TranslationHelper::translate('Monthly'), $html);
        $this->assertStringContainsString('300.00', $html);
        $this->assertStringContainsString('3,000.00', $html);
    }

    public function test_show_page_aborts_without_view_packages_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions([]));

        $package = $this->createPackage();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new PackageController())->show($package->id);
    }
}
