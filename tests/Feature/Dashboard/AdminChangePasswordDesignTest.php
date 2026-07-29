<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\AdminController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The admin "change password" page (/admin/admins/change-password/{id})
 * was restyled to match a design reference: a lock icon badge next to the
 * title, password fields with a leading lock icon + a show/hide eye toggle
 * (masked by default — @selected/@checked-style bugs aside, this is plain
 * type="password" that JS flips to type="text" only on click), a tip
 * banner, and a full-width save button. The form's action/fields/route
 * are unchanged — this is a visual-only pass.
 */
class AdminChangePasswordDesignTest extends TestCase
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

        $permission = Permission::firstOrCreate(['name' => 'edit admin', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    public function test_password_inputs_are_masked_by_default()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminController())->change_password_form($admin->id)->render();

        $this->assertStringContainsString('type="password" name="password"', $html);
        $this->assertStringContainsString('type="password" name="password_confirmation"', $html);
        $this->assertStringNotContainsString('type="text" name="password"', $html);
    }

    public function test_page_has_a_show_hide_toggle_button_for_each_password_field()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminController())->change_password_form($admin->id)->render();

        $this->assertSame(2, substr_count($html, 'class="md-password-toggle"'));
        $this->assertStringContainsString('data-target="password"', $html);
        $this->assertStringContainsString('data-target="password_confirmation"', $html);
    }

    public function test_page_shows_the_lock_icon_badge_and_password_tip()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminController())->change_password_form($admin->id)->render();

        $this->assertStringContainsString('md-page-icon', $html);
        $this->assertStringContainsString('md-password-tip', $html);
        $this->assertStringContainsString(TranslationHelper::translate('password_tip'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('password_must_be_strong'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('reenter_password_to_confirm'), $html);
    }

    public function test_form_still_submits_to_the_real_save_password_route()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminController())->change_password_form($admin->id)->render();

        $this->assertStringContainsString(route('admin.admins.save_password', $admin->id), $html);
    }
}
