<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class AdministratorPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'Super Admin')->first();

        Permission::create(['name' => 'view admins', 'guard_name' => 'admin']);
        Permission::create(['name' => 'add admin', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit admin', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete admin', 'guard_name' => 'admin']);
        
        $role->givePermissionTo('view admins');
        $role->givePermissionTo('add admin');
        $role->givePermissionTo('edit admin');
        $role->givePermissionTo('delete admin');

        Permission::create(['name' => 'view roles', 'guard_name' => 'admin']);
        Permission::create(['name' => 'add role', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit role', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete role', 'guard_name' => 'admin']);
        
        $role->givePermissionTo('view roles');
        $role->givePermissionTo('add role');
        $role->givePermissionTo('edit role');
        $role->givePermissionTo('delete role');

    }
}
