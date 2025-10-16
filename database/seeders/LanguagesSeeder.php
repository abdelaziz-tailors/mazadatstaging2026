<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::updateOrCreate(
            [
            'code' => 'ar',
            ],[
            'name' => json_encode([
                'en' => 'Arabic',
                'ar' => 'عربي'
            ]),
            'code' => 'ar'
        ]);

        Language::updateOrCreate(
            [
            'code' => 'en',
            ],[
            'name' => json_encode([
                'en' => 'English',
                'ar' => 'انجليزي'
            ]),
            'code' => 'en'

        ]);

        Language::updateOrCreate(
            [
            'code' => 'ur',
            ],[
            'name' => json_encode([
                'en' => 'Urdu',
                'ar' => 'الأردية'
            ]),

            'code' => 'ur',
        ]);
    }
}
