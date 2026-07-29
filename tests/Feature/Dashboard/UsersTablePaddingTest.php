<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\UserController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: the users list page overrides DataTables' default "dom"
 * layout with custom "d-flex" wrapper divs instead of the standard
 * ".row"/".col-*" markup. The app-wide theme.css fix for the export
 * buttons/length-menu hugging the card edge only targets that standard
 * ".row > [class^=col-]" structure, so it never applied here — this page's
 * own toolbar wrappers needed their own horizontal padding added directly
 * in the "dom" string.
 */
class UsersTablePaddingTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithViewUsersPermission(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        $permission = Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    public function test_datatable_toolbar_and_pagination_wrappers_have_horizontal_padding()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserController())->index(new Request())->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
        $this->assertStringContainsString('d-flex justify-content-between px-2', $html);
    }
}
