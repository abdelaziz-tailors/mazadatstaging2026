<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\RoleController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The roles list (/admin/roles) had a generic "fa-cogs" dropdown-only
 * actions column and no descriptive search placeholder. This restyles
 * actions to the round icon-button component and adds a search
 * placeholder — same visual pass already applied to the other small
 * admin lookup pages this session (ages/colors/categories/sliders/
 * notifications). Per a later explicit follow-up request, the delete
 * action was pulled out of a kebab dropdown into its own standalone
 * red icon button (edit + delete side by side, no dropdown at all).
 */
class RolesActionsAndSearchTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(array $permissions = ['view roles', 'edit role', 'delete role']): Admin
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

    private function createRole(): Role
    {
        return Role::create(['name' => 'Test Role ' . random_int(100000, 999999), 'guard_name' => 'admin']);
    }

    private function rowFor(Role $role): array
    {
        $request = Request::create('/admin/roles/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new RoleController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $role->id);
    }

    public function test_actions_are_icon_style_with_standalone_edit_and_delete_icons()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        $role = $this->createRole();

        $row = $this->rowFor($role);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString('md-icon-btn-danger', $row['action']);
        $this->assertStringContainsString(route('admin.roles.edit', $role->id), $row['action']);
        $this->assertStringContainsString('#deleteModal-' . $role->id, $row['action']);
        $this->assertStringContainsString('fa-trash', $row['action']);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $row['action']);
        $this->assertStringNotContainsString('dropdown', $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    public function test_edit_icon_is_hidden_without_edit_role_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view roles']));
        $role = $this->createRole();

        $row = $this->rowFor($role);

        $this->assertStringNotContainsString(route('admin.roles.edit', $role->id), $row['action']);
    }

    public function test_delete_icon_is_hidden_without_delete_role_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view roles', 'edit role']));
        $role = $this->createRole();

        $row = $this->rowFor($role);

        $this->assertStringNotContainsString('md-icon-btn-danger', $row['action']);
        $this->assertStringNotContainsString('#deleteModal-' . $role->id, $row['action']);
    }

    public function test_role_id_1_never_shows_a_delete_action_regardless_of_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $protectedRole = Role::where('id', 1)->first();
        if (!$protectedRole) {
            $this->markTestSkipped('Role id 1 does not exist in this test database.');
        }

        $row = $this->rowFor($protectedRole);

        $this->assertStringNotContainsString('md-icon-btn-danger', $row['action']);
    }

    public function test_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new RoleController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_roles_placeholder'), $html);
    }

    public function test_index_page_toolbar_has_horizontal_padding()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new RoleController())->index()->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
    }
}
