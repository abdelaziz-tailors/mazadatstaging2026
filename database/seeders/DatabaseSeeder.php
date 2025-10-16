<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User\User::factory(10)->create();

        $this->call([
            DepartmentPermissionSeeder::class,
            ProviderPermissionSeeder::class,
            ClientsPermissionSeeder::class,
            // LanguagesSeeder::class,
            // LanguagePermissionSeeder::class,
            KsaCitiesSeeder::class,


        ]);
    }
}
