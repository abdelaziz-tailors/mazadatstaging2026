<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: the edit/delete icons in the users table's "settings"
 * column used to sit inside a ".btn-group" wrapper that only adds spacing
 * between real ".btn" elements, so they sat flush against each other with
 * no gap. Switched the wrapper to a flex row with an explicit gap. The
 * icons themselves were later restyled from ".dropdown-item" text links to
 * the round ".md-icon-btn" component shared with the Auctions table.
 */
class UserActionsButtonSpacingTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach (['edit user', 'delete user'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_edit_and_delete_icons_have_a_gap_between_them_and_are_not_full_width_blocks()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $user = User::create([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);

        $html = view('dashboard.pages.users.actions', ['item' => $user])->render();

        $this->assertStringContainsString('d-flex align-items-center gap-2', $html);
        $this->assertStringNotContainsString('btn-group', $html);
        $this->assertStringContainsString('md-icon-btn', $html);
    }
}
