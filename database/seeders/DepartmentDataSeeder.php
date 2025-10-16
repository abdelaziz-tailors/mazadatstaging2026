<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create([
            'name' => json_encode([
                'en' => 'Medical Section',
                'ar' => 'القسم الطبي'
            ]),
            'slug' => 'medical_section',
        ]);

        Department::create([
            'name' => json_encode([
                'en' => 'Services Section',
                'ar' => 'قسم الخدمات'
            ]),
            'slug' => 'services_section',
        ]);

        Department::create([
            'name' => json_encode([
                'en' => 'Medical Store',
                'ar' => 'المتجر الطبي'
            ]),
            'slug' => 'medical_store',
        ]);
    }
}
