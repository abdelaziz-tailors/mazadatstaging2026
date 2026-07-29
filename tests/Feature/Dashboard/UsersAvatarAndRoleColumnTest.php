<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\UserController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The users list (e.g. /admin/users?user_type=buyer) showed no avatar and
 * no account-type/role column at all, and its row actions used the old
 * colored inline-icon style instead of the round icon-button component
 * used elsewhere. This adds both missing columns (reusing the same
 * "dashboard.partials.avatar" component already used for the admin header/
 * profile fix, so a missing/broken image falls back to a placeholder
 * instead of a broken <img>) and restyles the actions to match.
 */
class UsersAvatarAndRoleColumnTest extends TestCase
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

        foreach (['view users', 'edit user', 'delete user'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    private function rowFor(User $user): array
    {
        $request = Request::create('/admin/users/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new UserController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $user->id);
    }

    public function test_image_column_renders_a_real_avatar_when_the_user_has_one()
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/me.png', 'fake-content');
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $user = $this->createUser(['image' => 'users/me.png']);

        $row = $this->rowFor($user);

        $this->assertStringContainsString('md-avatar"', $row['image']);
        $this->assertStringContainsString(Storage::disk('public')->url('users/me.png'), $row['image']);
    }

    public function test_image_column_renders_a_placeholder_when_the_user_has_no_image()
    {
        Storage::fake('public');
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $user = $this->createUser(['image' => null]);

        $row = $this->rowFor($user);

        $this->assertStringContainsString('md-avatar-placeholder', $row['image']);
    }

    public function test_account_type_column_shows_the_translated_value_when_set()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $individual = $this->createUser(['account_type' => 'individual']);
        $company = $this->createUser(['account_type' => 'company']);

        $individualRow = $this->rowFor($individual);
        $companyRow = $this->rowFor($company);

        $this->assertEquals(TranslationHelper::translate('individual'), $individualRow['account_type']);
        $this->assertEquals(TranslationHelper::translate('company'), $companyRow['account_type']);
    }

    public function test_account_type_column_falls_back_to_a_dash_when_not_set()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $user = $this->createUser(['account_type' => null]);

        $row = $this->rowFor($user);

        $this->assertEquals('-', $row['account_type']);
    }

    /**
     * The "account type" column is redundant once the list is already
     * filtered to a single user_type (e.g. /admin/users?user_type=buyer —
     * every row is a buyer, so the column just repeats the page title) —
     * per explicit request, dropped for that filtered view specifically.
     * The general, unfiltered /admin/users list still needs it (it mixes
     * buyers/vendors/sellers).
     */
    public function test_account_type_column_is_hidden_on_the_buyers_filtered_view()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $request = Request::create('/admin/users', 'GET', ['user_type' => 'buyer']);

        $html = (new UserController())->index($request)->render();

        $this->assertStringNotContainsString(TranslationHelper::translate('account_type'), $html);
        $this->assertStringNotContainsString("data: 'account_type'", $html);
    }

    public function test_account_type_column_still_shows_on_the_general_unfiltered_users_view()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $request = Request::create('/admin/users', 'GET');

        $html = (new UserController())->index($request)->render();

        $this->assertStringContainsString(TranslationHelper::translate('account_type'), $html);
        $this->assertStringContainsString("data: 'account_type'", $html);
    }

    /**
     * Regression guard: actions are round icon buttons — view + edit are
     * plain monochrome, and delete is now a standalone red danger icon (no
     * kebab dropdown), matching the roles-page pattern.
     */
    public function test_actions_are_round_monochrome_icon_buttons_not_the_old_colored_inline_icons()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());

        $user = $this->createUser();

        $row = $this->rowFor($user);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString('md-icon-btn-danger', $row['action']);
        $this->assertStringContainsString('fa-trash', $row['action']);
        $this->assertStringContainsString('fa-pen', $row['action']);
        $this->assertStringContainsString('fa-eye', $row['action']);
        $this->assertStringContainsString(route('admin.users.show', $user->id), $row['action']);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $row['action']);
        $this->assertStringNotContainsString('dropdown-menu', $row['action']);
        $this->assertStringNotContainsString('md-icon-btn-info', $row['action']);
        $this->assertStringNotContainsString('md-icon-btn-success', $row['action']);
        $this->assertStringNotContainsString('style="color: #2196f3"', $row['action']);
        $this->assertStringNotContainsString('style="color: #e42f2f"', $row['action']);
    }

    /**
     * Regression guard: the search box's placeholder was just the generic
     * "search" word, giving no hint of which fields it actually matches
     * against. It now spells out "search by name or email" (the fields
     * the columns array actually marks searchable).
     */
    public function test_search_box_has_a_placeholder_describing_which_fields_it_searches()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new UserController())->index(new Request())->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_users_placeholder'), $html);
    }
}
