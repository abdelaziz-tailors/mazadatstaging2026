<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BannerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'Super Admin')->first();

        Permission::create(['name' => 'add banner', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit banner', 'guard_name' => 'admin']);
        Permission::create(['name' => 'delete banner', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view banners', 'guard_name' => 'admin']);

        $role->givePermissionTo('add banner');
        $role->givePermissionTo('edit banner');
        $role->givePermissionTo('delete banner');
        $role->givePermissionTo('view banners');
    }
}
