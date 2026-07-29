<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: on /admin/users, /admin/users?user_type=buyer, and
 * /admin/partners, the kebab (⋮) menu used to be the first element in the
 * actions flex row — which, in RTL, renders it as the rightmost icon. Per
 * explicit request it must sit at the far left instead, with "view" as the
 * rightmost icon and "edit" in between. In a "d-flex" row under RTL, DOM
 * order right-to-left is: first child = rightmost, last child = leftmost —
 * so the fix is DOM order: view, then edit, then the kebab last.
 */
class ActionsIconOrderTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithAllPermissions(array $names): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach ($names as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    /**
     * Users no longer has a kebab menu at all — delete was pulled out into
     * its own standalone red icon (see UserBuyersFilterPanelTest), so this
     * checks the DOM order view -> edit -> delete instead of view -> edit -> kebab.
     */
    public function test_users_table_actions_are_ordered_view_then_edit_then_delete_last()
    {
        Auth::guard('admin')->setUser($this->createAdminWithAllPermissions(['edit user', 'delete user']));

        $user = User::create([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);

        $html = view('dashboard.pages.users.actions', ['item' => $user])->render();

        $viewPos = strpos($html, route('admin.users.show', $user->id));
        $editPos = strpos($html, route('admin.users.edit', $user->id));
        $deletePos = strpos($html, 'md-icon-btn-danger');

        $this->assertNotFalse($viewPos);
        $this->assertNotFalse($editPos);
        $this->assertNotFalse($deletePos);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertLessThan($editPos, $viewPos);
        $this->assertLessThan($deletePos, $editPos);
    }

    public function test_partners_table_actions_are_ordered_view_then_edit_then_kebab_last()
    {
        Auth::guard('admin')->setUser($this->createAdminWithAllPermissions(['view partners', 'edit partner', 'delete partner']));

        $partnerUser = User::create([
            'name' => 'Partner User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
        $partner = Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'user_id' => $partnerUser->id,
        ]);

        $html = view('dashboard.pages.partners.actions', ['item' => $partner])->render();

        $viewPos = strpos($html, route('admin.partners.show', $partner->id));
        $editPos = strpos($html, route('admin.partners.edit', $partner->id));
        $kebabPos = strpos($html, 'fa-ellipsis-vertical');

        $this->assertNotFalse($viewPos);
        $this->assertNotFalse($editPos);
        $this->assertNotFalse($kebabPos);
        $this->assertLessThan($editPos, $viewPos);
        $this->assertLessThan($kebabPos, $editPos);
    }
}
