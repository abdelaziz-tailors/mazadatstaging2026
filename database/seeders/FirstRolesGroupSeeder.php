<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FirstRolesGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);

        Permission::create(['name' => 'add city', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit city', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete city', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view cities', 'guard_name' => 'admin']);

        Permission::create(['name' => 'add country', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit country', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete country', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view countries', 'guard_name' => 'admin']);

        $role->givePermissionTo('add city');
        $role->givePermissionTo('edit city');
        $role->givePermissionTo('delete city');
        $role->givePermissionTo('view cities');

        $role->givePermissionTo('add country');
        $role->givePermissionTo('edit country');
        $role->givePermissionTo('delete country');
        $role->givePermissionTo('view countries');
    }
}
