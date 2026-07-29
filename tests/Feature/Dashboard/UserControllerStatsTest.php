<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\UserController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Hits the controller directly rather than the HTTP route — see the note in
 * OrderControllerStatsTest for why (a pre-existing dashboard-locale redirect
 * quirk unrelated to this feature).
 */
class UserControllerStatsTest extends TestCase
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

        $permission = Permission::firstOrCreate([
            'name' => 'view users',
            'guard_name' => 'admin',
        ]);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    private function createUser(string $userType, bool $active = true, bool $verified = false): User
    {
        return User::create([
            'name' => 'User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => $userType,
            'gender' => 'male',
            'is_active' => $active,
            'is_verified' => $verified,
        ]);
    }

    /**
     * The stats are a global count over a real, shared testing database, so
     * assertions compare before/after deltas instead of exact totals.
     */
    public function test_index_computes_correct_stats_by_user_type_when_unfiltered()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());

        $baseline = User::query()->selectRaw("
            sum(user_type = 'buyer') as buyers,
            sum(user_type = 'vendor') as vendors,
            sum(user_type = 'seller') as sellers,
            count(*) as total
        ")->first();

        $this->createUser('buyer');
        $this->createUser('buyer');
        $this->createUser('vendor');
        $this->createUser('seller');

        $view = (new UserController())->index(new Request());
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->buyers + 2, $stats['buyers']);
        $this->assertEquals((int) $baseline->vendors + 1, $stats['vendors']);
        $this->assertEquals((int) $baseline->sellers + 1, $stats['sellers']);
        $this->assertEquals((int) $baseline->total + 4, $stats['total']);
    }

    public function test_index_computes_correct_stats_scoped_to_user_type_when_filtered()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());

        $baseline = User::query()->where('user_type', 'buyer')->selectRaw("
            sum(is_active = 1) as active,
            sum(is_active = 0) as inactive,
            sum(is_verified = 1) as verified,
            count(*) as total
        ")->first();

        $this->createUser('buyer', active: true, verified: true);
        $this->createUser('buyer', active: false, verified: false);
        $this->createUser('vendor', active: true, verified: true); // different type, must not count

        $request = new Request(['user_type' => 'buyer']);
        $view = (new UserController())->index($request);
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->active + 1, $stats['active']);
        $this->assertEquals((int) $baseline->inactive + 1, $stats['inactive']);
        $this->assertEquals((int) $baseline->verified + 1, $stats['verified']);
        $this->assertEquals((int) $baseline->total + 2, $stats['total']);
    }

    public function test_index_renders_the_buyers_title_when_filtered_by_user_type()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());
        view()->share('errors', new ViewErrorBag());

        $request = new Request(['user_type' => 'buyer']);
        $view = (new UserController())->index($request);

        $this->assertArrayHasKey('stats', $view->getData());
        $this->assertStringContainsString(TranslationHelper::translate('total_buyers'), $view->render());
    }

    /**
     * get_data() used to hardcode "user_type = buyer_vendor" regardless of
     * what was requested — a leftover from a copy-pasted scaffold — so the
     * table always showed "no data" even though real buyer/vendor/seller
     * users existed (visible in the page's own stat brief above the table).
     * These call the controller directly with the request bound into the
     * container, since Yajra's DataTables facade resolves the current
     * request from there rather than from the injected method parameter.
     */
    public function test_get_data_lists_all_real_users_when_unfiltered()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());

        $buyer = $this->createUser('buyer');
        $vendor = $this->createUser('vendor');
        $seller = $this->createUser('seller');

        $request = Request::create('/admin/users/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new UserController())->get_data($request);
        $ids = collect(json_decode($response->getContent(), true)['data'])->pluck('id');

        $this->assertTrue($ids->contains($buyer->id));
        $this->assertTrue($ids->contains($vendor->id));
        $this->assertTrue($ids->contains($seller->id));
    }

    public function test_get_data_filters_by_user_type_when_provided()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());

        $buyer = $this->createUser('buyer');
        $vendor = $this->createUser('vendor');

        $request = Request::create('/admin/users/getData', 'POST', [
            'draw' => 1, 'start' => 0, 'length' => 50, 'user_type' => 'buyer',
        ]);
        app()->instance('request', $request);

        $response = (new UserController())->get_data($request);
        $ids = collect(json_decode($response->getContent(), true)['data'])->pluck('id');

        $this->assertTrue($ids->contains($buyer->id));
        $this->assertFalse($ids->contains($vendor->id));
    }
}
