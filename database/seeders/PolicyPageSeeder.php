<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PolicyPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Page::create(['slug' => 'terms_and_conditions', 'name'=>json_encode(['en' => 'Terms And Conditions', 'ar' => 'الشروط و  الاحكام']), 'content'=>json_encode(['en' => 'Terms And Conditions', 'ar' => 'الشروط و  الاحكام'])]);
        Page::create(['slug' => 'privacy_policy', 'name'=>json_encode(['en' => 'Privacy Policy', 'ar' => 'سياسة الخصوصية']), 'content'=>json_encode(['en' => 'Privacy Policy', 'ar' => 'سياسة الخصوصية'])]);
    }
}
