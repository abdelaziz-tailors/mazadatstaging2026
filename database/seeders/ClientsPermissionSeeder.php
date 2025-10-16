<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClientsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'Super Admin')->first();

        Permission::updateOrCreate(
            ['name' => 'add client', 'guard_name' => 'admin'],
            ['name' => 'add client', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'edit client', 'guard_name' => 'admin'],
            ['name' => 'edit client', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'delete client', 'guard_name' => 'admin'],
            ['name' => 'delete client', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'view client', 'guard_name' => 'admin'],
            ['name' => 'view client', 'guard_name' => 'admin']
        );

        $role->givePermissionTo('view client');
        $role->givePermissionTo('add client');
        $role->givePermissionTo('edit client');
        $role->givePermissionTo('delete client');
    }
}
