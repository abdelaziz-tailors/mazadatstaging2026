<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NationalityPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'Super Admin')->first();

        Permission::create(['name' => 'add nationality', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit nationality', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete nationality', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view nationalities', 'guard_name' => 'admin']);

        $role->givePermissionTo('add nationality');
        $role->givePermissionTo('edit nationality');
        $role->givePermissionTo('delete nationality');
        $role->givePermissionTo('view nationalities');
    }
}
