<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\Permissions;
use App\Http\Controllers\Dashboard\UserSubscriptionController;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserSubscriptionPermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Laravel\Telescope\Telescope::stopRecording();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        require_once database_path('migrations/New folder/2022_10_16_092804_create_permission_tables.php');
        (new \CreatePermissionTables)->up();
        require_once database_path('migrations/2026_10_26_000002_add_user_subscription_permissions.php');
    }

    public function test_migration_grants_super_admin_access_and_is_repeatable(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $existing = Permission::create(['name' => 'view packages', 'guard_name' => 'admin']);
        $role->givePermissionTo($existing);
        $otherRole = Role::create(['name' => 'Support', 'guard_name' => 'admin']);
        (new \AddUserSubscriptionPermissions)->up();
        (new \AddUserSubscriptionPermissions)->up();

        // A persisted admin ID is enough to exercise the real role and Gate checks.
        $admin = new Admin;
        $admin->id = 123;
        $admin->exists = true;
        $admin->assignRole($role);
        Auth::guard('admin')->setUser($admin);

        $this->assertTrue((new UserSubscriptionController)->authorizable('view user subscriptions'));
        $this->assertTrue($admin->can('approve user subscription'));
        $this->assertTrue($admin->can('reject user subscription'));
        $this->assertTrue($admin->can('view packages'));
        $this->assertCount(0, $otherRole->fresh()->permissions);

        $catalog = collect((new Permissions)->get_permissions())->firstWhere('name', 'user subscriptions');
        foreach ($role->fresh()->permissions->where('name', '!=', 'view packages') as $permission) {
            $this->assertArrayHasKey($permission->name, $catalog['permissions']);
        }
    }

    public function test_admin_without_permission_is_still_denied(): void
    {
        (new \AddUserSubscriptionPermissions)->up();
        $admin = new Admin;
        $admin->id = 456;
        Auth::guard('admin')->setUser($admin);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('THIS ACTION IS UNAUTHORIZED.');
        (new UserSubscriptionController)->authorizable('view user subscriptions');
    }
}
