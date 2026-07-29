<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\UserController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * New read-only "view" page for a single user, linked from the eye icon in
 * the users table's actions column (per explicit request — the users list
 * had no dedicated view page at all before, only edit).
 */
class UserShowPageTest extends TestCase
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

    public function test_show_page_renders_the_users_details()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());
        view()->share('errors', new ViewErrorBag());

        $user = User::create([
            'name' => 'Jane Buyer',
            'user_name' => 'jane_b',
            'email' => 'jane@example.com',
            'phone' => '0100000001',
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'account_type' => 'individual',
            'gender' => 'female',
            'is_active' => 1,
        ]);

        $html = (new UserController())->show($user->id)->render();

        $this->assertStringContainsString('Jane Buyer', $html);
        $this->assertStringContainsString('jane@example.com', $html);
        $this->assertStringContainsString('0100000001', $html);
    }

    public function test_show_page_renders_a_real_avatar_when_the_user_has_one()
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/jane.png', 'fake-content');
        Auth::guard('admin')->setUser($this->createAdminWithViewUsersPermission());
        view()->share('errors', new ViewErrorBag());

        $user = User::create([
            'name' => 'Jane Buyer',
            'phone' => '0100000002',
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'female',
            'image' => 'users/jane.png',
        ]);

        $html = (new UserController())->show($user->id)->render();

        $this->assertStringContainsString('md-avatar"', $html);
        $this->assertStringContainsString(Storage::disk('public')->url('users/jane.png'), $html);
    }

    public function test_show_page_aborts_when_admin_lacks_view_users_permission()
    {
        $admin = Admin::create([
            'name' => 'No Permission Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        Auth::guard('admin')->setUser($admin);

        $user = User::create([
            'name' => 'Jane Buyer',
            'phone' => '0100000003',
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'female',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new UserController())->show($user->id);
    }
}
