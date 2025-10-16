<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class SliderPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'Super Admin')->first();

        Permission::create(['name' => 'add slider', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit slider', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete slider', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view sliders', 'guard_name' => 'admin']);

        $role->givePermissionTo('add slider');
        $role->givePermissionTo('edit slider');
        $role->givePermissionTo('delete slider');
        $role->givePermissionTo('view sliders');
    }
}
