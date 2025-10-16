<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class DepartmentPermissionSeeder extends Seeder
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
            ['name' => 'add department', 'guard_name' => 'admin'],
            ['name' => 'add department', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'edit department', 'guard_name' => 'admin'],
            ['name' => 'edit department', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'delete department', 'guard_name' => 'admin'],
            ['name' => 'delete department', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'view departments', 'guard_name' => 'admin'],
            ['name' => 'view departments', 'guard_name' => 'admin']
        );

        $role->givePermissionTo('add department');
        $role->givePermissionTo('edit department');
        $role->givePermissionTo('delete department');
        $role->givePermissionTo('view departments');
    }
}
