<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class CategoryPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'Super Admin')->first();

        Permission::create(['name' => 'add category', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit category', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete category', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view categories', 'guard_name' => 'admin']);

        $role->givePermissionTo('add category');
        $role->givePermissionTo('edit category');
        $role->givePermissionTo('delete category');
        $role->givePermissionTo('view categories');
    }
}
