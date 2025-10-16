<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DropSliderPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::get();
        foreach ($roles as $role) {
            $role->revokePermissionTo('add slider');
            $role->revokePermissionTo('delete slider');
            $role->revokePermissionTo('edit slider');
            $role->revokePermissionTo('view sliders');
        }
        Permission::where('name', 'add slider')->delete();
        Permission::where('name', 'delete slider')->delete();
        Permission::where('name', 'edit slider')->delete();
        Permission::where('name', 'view sliders')->delete();
    }
}
