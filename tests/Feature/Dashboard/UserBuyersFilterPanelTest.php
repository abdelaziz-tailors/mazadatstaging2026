<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\UserController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the buyers-page filter panel added on top of UserController::get_data():
 * username, email, status, and date range filters — all built from real,
 * already-stored columns (name/email/is_active/created_at), no new schema.
 * The registration-platform filter was added then explicitly removed by the
 * user. See UserControllerStatsTest for why the controller is hit directly
 * instead of the HTTP route.
 */
class UserBuyersFilterPanelTest extends TestCase
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

        foreach (['view users', 'delete user'] as $permName) {
            $permission = Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'admin',
            ]);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'User ' . random_int(100000, 999999),
            'email' => 'user' . random_int(100000, 999999) . '@example.com',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
            'is_active' => true,
            'is_verified' => false,
        ], $overrides));
    }

    private function callGetData(array $params)
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $request = Request::create('/admin/users/getData', 'POST', array_merge([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $params));
        app()->instance('request', $request);

        $response = (new UserController())->get_data($request);

        return collect(json_decode($response->getContent(), true)['data'])->pluck('id');
    }

    public function test_filter_username_matches_by_partial_name()
    {
        $match = $this->createUser(['name' => 'Ahmed Special Buyer']);
        $other = $this->createUser(['name' => 'Someone Else']);

        $ids = $this->callGetData(['filter_username' => 'Special Buyer']);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_email_matches_by_partial_email()
    {
        $match = $this->createUser(['email' => 'unique.match@example.com']);
        $other = $this->createUser(['email' => 'nomatch@example.com']);

        $ids = $this->callGetData(['filter_email' => 'unique.match']);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_filter_status_active_excludes_inactive_users()
    {
        $active = $this->createUser(['is_active' => true]);
        $inactive = $this->createUser(['is_active' => false]);

        $ids = $this->callGetData(['filter_status' => '1']);

        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($inactive->id));
    }

    public function test_filter_status_inactive_excludes_active_users()
    {
        $active = $this->createUser(['is_active' => true]);
        $inactive = $this->createUser(['is_active' => false]);

        $ids = $this->callGetData(['filter_status' => '0']);

        $this->assertFalse($ids->contains($active->id));
        $this->assertTrue($ids->contains($inactive->id));
    }

    public function test_filter_date_range_excludes_users_outside_the_range()
    {
        $inRange = $this->createUser();
        $inRange->created_at = now()->subDays(5);
        $inRange->save();

        $outOfRange = $this->createUser();
        $outOfRange->created_at = now()->subDays(30);
        $outOfRange->save();

        $ids = $this->callGetData([
            'filter_date_from' => now()->subDays(10)->toDateString(),
            'filter_date_to' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_combined_filters_apply_together_as_an_intersection()
    {
        $match = $this->createUser(['name' => 'Combo Match', 'is_active' => true]);
        $wrongName = $this->createUser(['name' => 'Nope', 'is_active' => true]);
        $wrongStatus = $this->createUser(['name' => 'Combo Match', 'is_active' => false]);

        $ids = $this->callGetData([
            'filter_username' => 'Combo Match',
            'filter_status' => '1',
        ]);

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($wrongName->id));
        $this->assertFalse($ids->contains($wrongStatus->id));
    }

    public function test_no_filters_returns_unfiltered_results()
    {
        $user = $this->createUser();

        $ids = $this->callGetData([]);

        $this->assertTrue($ids->contains($user->id));
    }

    public function test_filter_panel_markup_is_rendered_on_the_buyers_view()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        $request = new Request(['user_type' => 'buyer']);
        $view = (new UserController())->index($request);
        $html = $view->render();

        $this->assertStringContainsString('id="usersFilterPanel"', $html);
        $this->assertStringContainsString('id="filter_username"', $html);
        $this->assertStringContainsString('id="filter_email"', $html);
        $this->assertStringContainsString('id="filter_status"', $html);
        $this->assertStringContainsString('id="filter_date_from"', $html);
        $this->assertStringContainsString('id="filter_date_to"', $html);
        $this->assertStringContainsString('id="filter_reset"', $html);
        $this->assertStringNotContainsString('id="filter_platform"', $html);
    }

    public function test_filter_panel_is_not_rendered_on_the_unfiltered_all_users_view()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        $view = (new UserController())->index(new Request());
        $html = $view->render();

        $this->assertStringNotContainsString('id="usersFilterPanel"', $html);
    }

    public function test_delete_icon_is_a_standalone_danger_icon_not_a_dropdown()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $user = $this->createUser();

        $html = view('dashboard.pages.users.actions', ['item' => $user])->render();

        $this->assertStringContainsString('md-icon-btn-danger', $html);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
    }

    public function test_delete_icon_is_hidden_without_delete_user_permission()
    {
        $admin = Admin::create([
            'name' => 'No Delete Admin',
            'email' => 'nodelete' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        $permission = Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);
        Auth::guard('admin')->setUser($admin);

        $user = $this->createUser();

        $html = view('dashboard.pages.users.actions', ['item' => $user])->render();

        $this->assertStringNotContainsString('md-icon-btn-danger', $html);
    }
}
