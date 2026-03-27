<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => ['en' => 'Syria', 'ar' => 'سوريا'], 'code' => '+963'],
            ['name' => ['en' => 'India', 'ar' => 'الهند'], 'code' => '+91'],
            ['name' => ['en' => 'Egypt', 'ar' => 'مصر'], 'code' => '+20'],
            ['name' => ['en' => 'Thailand', 'ar' => 'تايلاند'], 'code' => '+66'],
            ['name' => ['en' => 'USA', 'ar' => 'أمريكا'], 'code' => '+1'],
            ['name' => ['en' => 'Italy', 'ar' => 'إيطاليا'], 'code' => '+39'],
            ['name' => ['en' => 'Pakistan', 'ar' => 'باكستان'], 'code' => '+92'],
            ['name' => ['en' => 'China', 'ar' => 'الصين'], 'code' => '+86'],
            ['name' => ['en' => 'Turkey', 'ar' => 'تركيا'], 'code' => '+90'],
            ['name' => ['en' => 'Bangladesh', 'ar' => 'بنغلاديش'], 'code' => '+880'],
            ['name' => ['en' => 'Canada', 'ar' => 'كندا'], 'code' => '+1'],
            ['name' => ['en' => 'France', 'ar' => 'فرنسا'], 'code' => '+33'],
        ];

        foreach ($countries as $country) {
            \App\Models\Country::create($country);
        }
    }
}
