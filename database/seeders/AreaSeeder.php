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
                'city_id' => 1
            ],
            [
                'name' => ['en' => 'Uptown', 'ar' => 'المدينة العليا'],
                'lat' => 40.787011,
                'lng' => -73.975368,
                'city_id' => 1
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}
