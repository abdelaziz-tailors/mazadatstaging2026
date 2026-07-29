<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\PartnerController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\VendorController;
use App\Models\Admin;
use App\Models\City;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Per explicit request ("كل الداشبورد (تغيير عام شامل)"), the dashboard's gold
 * accent (--md-accent) was replaced dashboard-wide with the sidebar's own
 * dark green (#16382b) via new --md-brand* variables, EXCEPT for the two
 * spots that render gold directly on top of an already dark-green surface
 * (the sidebar's own active-menu highlight, and the wallet card icon/amount)
 * — there, switching to green would make the accent blend into its own
 * background, so those two rules deliberately keep using the original
 * --md-accent* (gold) variables untouched.
 */
class DashboardBrandGreenThemeTest extends TestCase
{
    use DatabaseTransactions;

    private function themeCss(): string
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        return $css;
    }

    private function createAdmin(array $permissions = []): Admin
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

    public function test_root_defines_the_brand_green_variables()
    {
        $css = $this->themeCss();

        $this->assertStringContainsString('--md-brand: #1f4a38;', $css);
        $this->assertStringContainsString('--md-brand-strong: #16382b;', $css);
        $this->assertStringContainsString('--md-brand-soft: rgba(22, 56, 43, 0.14);', $css);
    }

    public function test_btn_primary_and_pagination_use_the_green_brand_variables_with_light_text()
    {
        $css = $this->themeCss();

        $btnPos = strpos($css, '.btn-primary {');
        $this->assertNotFalse($btnPos);
        $btnBlock = substr($css, $btnPos, 300);
        $this->assertStringContainsString('linear-gradient(135deg, var(--md-brand), var(--md-brand-strong))', $btnBlock);
        $this->assertStringContainsString('color: #ffffff;', $btnBlock);

        $pagePos = strpos($css, '.dataTables_wrapper .pagination .page-item.active .page-link {');
        $this->assertNotFalse($pagePos);
        $pageBlock = substr($css, $pagePos, 200);
        $this->assertStringContainsString('background: var(--md-brand);', $pageBlock);
        $this->assertStringContainsString('color: #ffffff;', $pageBlock);
    }

    public function test_generic_dashboard_accents_use_the_green_brand_variables()
    {
        $css = $this->themeCss();

        $this->assertStringContainsString("a {\n  color: var(--md-brand-strong);\n}", $css);
        $this->assertStringContainsString("a:hover {\n  color: var(--md-brand);\n}", $css);

        $pageIconPos = strpos($css, '.md-page-icon {');
        $this->assertNotFalse($pageIconPos);
        $pageIconBlock = substr($css, $pageIconPos, 300);
        $this->assertStringContainsString('background: var(--md-brand-soft);', $pageIconBlock);
        $this->assertStringContainsString('color: var(--md-brand-strong);', $pageIconBlock);

        $this->assertStringContainsString(".page-header .breadcrumb-item.active {\n  color: var(--md-brand-strong);\n}", $css);
        $this->assertStringContainsString('.stat-icon-primary { background: var(--md-brand-soft); color: var(--md-brand-strong); }', $css);
        $this->assertStringContainsString(".table > tbody a:not(.btn) {\n  color: var(--md-brand-strong);\n}", $css);
    }

    public function test_sidebar_active_item_and_wallet_card_keep_the_original_gold_accent()
    {
        $css = $this->themeCss();

        $sidebarPos = strpos($css, '.sidebar-menu ul li.active > a,');
        $this->assertNotFalse($sidebarPos);
        $sidebarBlock = substr($css, $sidebarPos, 200);
        $this->assertStringContainsString('linear-gradient(135deg, var(--md-accent), var(--md-accent-strong))', $sidebarBlock);

        $walletIconPos = strpos($css, '.md-wallet-icon {');
        $this->assertNotFalse($walletIconPos);
        $walletIconBlock = substr($css, $walletIconPos, 300);
        $this->assertStringContainsString('color: var(--md-accent);', $walletIconBlock);

        $walletAmountPos = strpos($css, '.md-wallet-amount {');
        $this->assertNotFalse($walletAmountPos);
        $walletAmountBlock = substr($css, $walletAmountPos, 100);
        $this->assertStringContainsString('color: var(--md-accent);', $walletAmountBlock);
    }

    public function test_the_old_scoped_brand_green_form_class_was_removed()
    {
        $css = $this->themeCss();

        $this->assertStringNotContainsString('.md-brand-green-form', $css);
    }

    public function test_vendor_create_page_no_longer_uses_the_removed_scoped_class()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        City::create(['name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']), 'is_active' => 1]);

        $html = (new VendorController())->create()->render();

        $this->assertStringNotContainsString('md-brand-green-form', $html);
        $this->assertMatchesRegularExpression('/class="card"/', $html);
    }

    public function test_buyer_create_page_no_longer_uses_the_removed_scoped_class()
    {
        Auth::guard('admin')->setUser($this->createAdmin(['add user']));
        view()->share('errors', new ViewErrorBag());

        $html = (new UserController())->create()->render();

        $this->assertStringNotContainsString('md-brand-green-form', $html);
        $this->assertMatchesRegularExpression('/class="card"/', $html);
    }

    public function test_partner_create_page_no_longer_uses_the_removed_scoped_class()
    {
        Auth::guard('admin')->setUser($this->createAdmin(['add partner']));
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerController())->create()->render();

        $this->assertStringNotContainsString('md-brand-green-form', $html);
        $this->assertMatchesRegularExpression('/class="card"/', $html);
    }
}
