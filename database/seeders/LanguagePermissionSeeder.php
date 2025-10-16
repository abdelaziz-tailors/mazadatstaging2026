<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LanguagePermissionSeeder extends Seeder
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
            ['name' => 'add language', 'guard_name' => 'admin'],
            ['name' => 'add language', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'edit language', 'guard_name' => 'admin'],
            ['name' => 'edit language', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'delete language', 'guard_name' => 'admin'],
            ['name' => 'delete language', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'view language', 'guard_name' => 'admin'],
            ['name' => 'view language', 'guard_name' => 'admin']
        );

        $role->givePermissionTo('view language');
        $role->givePermissionTo('add language');
        $role->givePermissionTo('edit language');
        $role->givePermissionTo('delete language');
    }
}
