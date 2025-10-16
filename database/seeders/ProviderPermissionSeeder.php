<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class ProviderPermissionSeeder extends Seeder
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
            ['name' => 'add provider', 'guard_name' => 'admin'],
            ['name' => 'add provider', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'edit provider', 'guard_name' => 'admin'],
            ['name' => 'edit provider', 'guard_name' => 'admin']
        );
        Permission::updateOrCreate(
            ['name' => 'delete provider', 'guard_name' => 'admin'],
            ['name' => 'delete provider', 'guard_name' => 'admin']
        );

        $role->givePermissionTo('add provider');
        $role->givePermissionTo('edit provider');
        $role->givePermissionTo('delete provider');
    }
}
