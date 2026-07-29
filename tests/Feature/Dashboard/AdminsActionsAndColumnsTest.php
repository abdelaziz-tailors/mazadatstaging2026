<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\AdminController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The admins list (/admin/admins) only showed name/email/role, had no
 * "view details" page, no avatar, and its row actions were a single
 * generic-looking dropdown. This adds a real read-only show page, an
 * avatar column (unified size, graceful placeholder), phone and
 * created-at columns, restyles the actions to the round icon-button
 * component (view/edit/kebab with change-password + delete) matching
 * the users/buyer table, and adds a descriptive search placeholder.
 */
class AdminsActionsAndColumnsTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(array $permissions = ['view admins', 'edit admin', 'delete admin']): Admin
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

    private function createManagedAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Managed Admin',
            'email' => 'managed' . random_int(100000, 999999) . '@example.com',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ], $overrides));
    }

    private function rowFor(Admin $target): array
    {
        $request = Request::create('/admin/admins/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new AdminController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $target->id);
    }

    public function test_get_data_includes_phone_and_created_at()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $target = $this->createManagedAdmin(['phone' => '0501234567']);

        $row = $this->rowFor($target);

        $this->assertEquals('0501234567', $row['phone']);
        $this->assertEquals($target->created_at->format('Y-m-d'), $row['created_at']);
    }

    public function test_get_data_renders_an_avatar_for_the_image_column()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $target = $this->createManagedAdmin();

        $row = $this->rowFor($target);

        $this->assertStringContainsString('md-avatar', $row['image']);
    }

    public function test_actions_have_standalone_view_and_edit_icons_plus_a_kebab_with_change_password_and_delete()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $target = $this->createManagedAdmin();

        $row = $this->rowFor($target);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.admins.show', $target->id), $row['action']);
        $this->assertStringContainsString(route('admin.admins.edit', $target->id), $row['action']);
        $this->assertStringContainsString(route('admin.admins.change-password', $target->id), $row['action']);
        $this->assertStringContainsString(TranslationHelper::translate('delete'), $row['action']);
        $this->assertStringContainsString('fa-ellipsis-vertical', $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    /**
     * Regression guard: the delete action here rendered in the default
     * dropdown-item color while the equivalent delete action on the
     * partners table (/admin/partners) was red — same destructive action,
     * inconsistent color. Matched to partners' "dropdown-item text-danger"
     * convention (theme.css already styles that combo red).
     */
    public function test_delete_action_is_styled_red_matching_the_partners_table()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $target = $this->createManagedAdmin();

        $row = $this->rowFor($target);

        $this->assertMatchesRegularExpression(
            '/dropdown-item text-danger[^>]*href="#deleteModal-' . $target->id . '"/',
            $row['action']
        );
    }

    public function test_view_and_edit_icons_are_standalone_not_inside_the_kebab_dropdown()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $target = $this->createManagedAdmin();

        $row = $this->rowFor($target);

        $dropdownMenu = substr($row['action'], strpos($row['action'], 'dropdown-menu'), 600);
        $this->assertStringNotContainsString(route('admin.admins.show', $target->id), $dropdownMenu);
        $this->assertStringNotContainsString(route('admin.admins.edit', $target->id), $dropdownMenu);
    }

    public function test_show_page_renders_the_admins_details()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $target = $this->createManagedAdmin(['name' => 'Jane Admin', 'phone' => '0559876543']);

        $html = (new AdminController())->show($target->id)->render();

        $this->assertStringContainsString('Jane Admin', $html);
        $this->assertStringContainsString('0559876543', $html);
    }

    public function test_show_page_aborts_without_view_admins_permission()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions([]));

        $target = $this->createManagedAdmin();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new AdminController())->show($target->id);
    }

    public function test_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_admins_placeholder'), $html);
    }

    public function test_index_page_renders_without_errors_and_has_padded_toolbar()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminController())->index()->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
    }
}
