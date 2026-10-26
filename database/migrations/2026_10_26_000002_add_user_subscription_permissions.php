<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AddUserSubscriptionPermissions extends Migration
{
    public function up()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();

        foreach (['view user subscriptions', 'approve user subscription', 'reject user subscription'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);

            if ($role) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        // Preserve permissions and grants that may have existed before this migration.
    }
}
