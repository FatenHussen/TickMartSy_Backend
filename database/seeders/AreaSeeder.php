<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => ['en' => 'Downtown', 'ar' => 'وسط المدينة'],
                'lat' => 40.712776,
                'lng' => -74.005974,
                'city_id' => 1,
                'base_fee' => 5
            ],
            [
                'name' => ['en' => 'Uptown', 'ar' => 'المدينة العليا'],
                'lat' => 40.787011,
                'lng' => -73.975368,
                'city_id' => 1,
                'base_fee' => 6
            ],
            [
                'name' => ['en' => 'Brooklyn', 'ar' => 'بروكلين'],
                'lat' => 40.650002,
                'lng' => -73.949997,
                'city_id' => 1,
                'base_fee' => 7
            ],
            [
                'name' => ['en' => 'Queens', 'ar' => 'كوينز'],
                'lat' => 40.728224,
                'lng' => -73.794852,
                'city_id' => 1,
                'base_fee' => 8
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}
