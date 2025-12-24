<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Governorate;
use App\Models\City;
use App\Models\Area;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Damascus' => [
                'ar' => 'دمشق',
                'cities' => [
                    'Damascus City' => [
                        'ar' => 'مدينة دمشق',
                        'areas' => [
                            ['ar' => 'المزة', 'lat' => 33.5138, 'lng' => 36.2765],
                            ['ar' => 'كفرسوسة', 'lat' => 33.4897, 'lng' => 36.2695],
                            ['ar' => 'المالكي', 'lat' => 33.5216, 'lng' => 36.2914],
                        ],
                    ],
                ],
            ],

            'Rif Damascus' => [
                'ar' => 'ريف دمشق',
                'cities' => [
                    'Douma' => [
                        'ar' => 'دوما',
                        'areas' => [
                            ['ar' => 'العب', 'lat' => 33.5712, 'lng' => 36.4022],
                            ['ar' => 'حرستا', 'lat' => 33.5582, 'lng' => 36.3667],
                        ],
                    ],
                    'Qudsaya' => [
                        'ar' => 'قدسيا',
                        'areas' => [
                            ['ar' => 'قدسيا البلد'],
                            ['ar' => 'ضاحية قدسيا'],
                        ],
                    ],
                ],
            ],

            'Aleppo' => [
                'ar' => 'حلب',
                'cities' => [
                    'Aleppo City' => [
                        'ar' => 'مدينة حلب',
                        'areas' => [
                            ['ar' => 'الحمدانية'],
                            ['ar' => 'السكري'],
                            ['ar' => 'صلاح الدين'],
                        ],
                    ],
                ],
            ],

            'Homs' => [
                'ar' => 'حمص',
                'cities' => [
                    'Homs City' => [
                        'ar' => 'مدينة حمص',
                        'areas' => [
                            ['ar' => 'الزهراء'],
                            ['ar' => 'الوعر'],
                        ],
                    ],
                ],
            ],

            'Latakia' => [
                'ar' => 'اللاذقية',
                'cities' => [
                    'Latakia City' => [
                        'ar' => 'مدينة اللاذقية',
                        'areas' => [
                            ['ar' => 'الرمل الجنوبي'],
                            ['ar' => 'مشروع الصليبة'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($data as $govKey => $govData) {
            $governorate = Governorate::create([
                'name' => [
                    'ar' => $govData['ar'],
                    'en' => $govKey,
                ],
            ]);

            foreach ($govData['cities'] as $cityKey => $cityData) {
                $city = City::create([
                    'name' => [
                        'ar' => $cityData['ar'],
                        'en' => $cityKey,
                    ],
                    'governorate_id' => $governorate->id,
                ]);

                foreach ($cityData['areas'] as $area) {
                    Area::create([
                        'name' => [
                            'ar' => $area['ar'],
                            'en' => $area['ar'],
                        ],
                        'lat' => $area['lat'] ?? null,
                        'lng' => $area['lng'] ?? null,
                        'city_id' => $city->id,
                    ]);
                }
            }
        }
    }
}
