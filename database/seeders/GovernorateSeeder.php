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
            // 1. Damascus
            'Damascus' => [
                'ar' => 'دمشق',
                'cities' => [
                    'Damascus City' => [
                        'ar' => 'مدينة دمشق',
                        'areas' => [
                            ['ar' => 'المزة', 'en' => 'Al-Mazzeh', 'lat' => 33.5138, 'lng' => 36.2765],
                            ['ar' => 'كفرسوسة', 'en' => 'Kafr Sousa', 'lat' => 33.4897, 'lng' => 36.2695],
                            ['ar' => 'المالكي', 'en' => 'Al-Malki', 'lat' => 33.5216, 'lng' => 36.2914],
                            ['ar' => 'أبو رمانة', 'en' => 'Abu Rummaneh', 'lat' => 33.5050, 'lng' => 36.2850],
                            ['ar' => 'المهاجرين', 'en' => 'Al-Muhajirin', 'lat' => 33.5200, 'lng' => 36.2900],
                        ],
                    ],
                ],
            ],

            // 2. Rif Damascus
            'Rif Damascus' => [
                'ar' => 'ريف دمشق',
                'cities' => [
                    'Douma' => [
                        'ar' => 'دوما',
                        'areas' => [
                            ['ar' => 'العب', 'en' => 'Al-Ebb', 'lat' => 33.5712, 'lng' => 36.4022],
                            ['ar' => 'حرستا', 'en' => 'Harasta', 'lat' => 33.5582, 'lng' => 36.3667],
                        ],
                    ],
                    'Qudsaya' => [
                        'ar' => 'قدسيا',
                        'areas' => [
                            ['ar' => 'قدسيا البلد', 'en' => 'Qudsaya Town'],
                            ['ar' => 'ضاحية قدسيا', 'en' => 'Qudsaya Suburb'],
                        ],
                    ],
                ],
            ],

            // 3. Aleppo
            'Aleppo' => [
                'ar' => 'حلب',
                'cities' => [
                    'Aleppo City' => [
                        'ar' => 'مدينة حلب',
                        'areas' => [
                            ['ar' => 'الحمدانية', 'en' => 'Al-Hamdaniyah', 'lat' => 36.2500, 'lng' => 37.1000],
                            ['ar' => 'السكري', 'en' => 'Al-Sukkari', 'lat' => 36.1800, 'lng' => 37.1200],
                            ['ar' => 'صلاح الدين', 'en' => 'Salah al-Din', 'lat' => 36.2000, 'lng' => 37.1500],
                        ],
                    ],
                ],
            ],

            // 4. Homs
            'Homs' => [
                'ar' => 'حمص',
                'cities' => [
                    'Homs City' => [
                        'ar' => 'مدينة حمص',
                        'areas' => [
                            ['ar' => 'الزهراء', 'en' => 'Al-Zahraa', 'lat' => 34.7300, 'lng' => 36.7200],
                            ['ar' => 'الوعر', 'en' => 'Al-Waer', 'lat' => 34.7000, 'lng' => 36.6800],
                            ['ar' => 'الخالدية', 'en' => 'Al-Khalidiyah', 'lat' => 34.7400, 'lng' => 36.7100],
                        ],
                    ],
                ],
            ],

            // 5. Latakia
            'Latakia' => [
                'ar' => 'اللاذقية',
                'cities' => [
                    'Latakia City' => [
                        'ar' => 'مدينة اللاذقية',
                        'areas' => [
                            ['ar' => 'الرمل الجنوبي', 'en' => 'Al-Raml Al-Janoubi', 'lat' => 35.5200, 'lng' => 35.7800],
                            ['ar' => 'مشروع الصليبة', 'en' => 'Al-Salibah Project', 'lat' => 35.5300, 'lng' => 35.7900],
                            ['ar' => 'الزراعة', 'en' => 'Al-Ziraa', 'lat' => 35.5100, 'lng' => 35.7700],
                        ],
                    ],
                ],
            ],

            // 6. Hama
            'Hama' => [
                'ar' => 'حماة',
                'cities' => [
                    'Hama City' => [
                        'ar' => 'مدينة حماة',
                        'areas' => [
                            ['ar' => 'الحاضر', 'en' => 'Al-Hadir', 'lat' => 35.1330, 'lng' => 36.7500],
                            ['ar' => 'الحميدية', 'en' => 'Al-Hamidiyah', 'lat' => 35.1400, 'lng' => 36.7600],
                        ],
                    ],
                ],
            ],

            // 7. Tartus
            'Tartus' => [
                'ar' => 'طرطوس',
                'cities' => [
                    'Tartus City' => [
                        'ar' => 'مدينة طرطوس',
                        'areas' => [
                            ['ar' => 'الثورة', 'en' => 'Al-Thawra', 'lat' => 34.8900, 'lng' => 35.8867],
                            ['ar' => 'الكورنيش', 'en' => 'Corniche', 'lat' => 34.8950, 'lng' => 35.8900],
                        ],
                    ],
                ],
            ],

            // 8. Idlib
            'Idlib' => [
                'ar' => 'إدلب',
                'cities' => [
                    'Idlib City' => [
                        'ar' => 'مدينة إدلب',
                        'areas' => [
                            ['ar' => 'المدينة', 'en' => 'City Center', 'lat' => 35.9300, 'lng' => 36.6300],
                            ['ar' => 'الشرقية', 'en' => 'Eastern District', 'lat' => 35.9350, 'lng' => 36.6350],
                        ],
                    ],
                ],
            ],

            // 9. Daraa
            'Daraa' => [
                'ar' => 'درعا',
                'cities' => [
                    'Daraa City' => [
                        'ar' => 'مدينة درعا',
                        'areas' => [
                            ['ar' => 'درعا البلد', 'en' => 'Daraa Al-Balad', 'lat' => 32.6189, 'lng' => 36.1022],
                            ['ar' => 'درعا المحطة', 'en' => 'Daraa Al-Mahatta', 'lat' => 32.6200, 'lng' => 36.1050],
                        ],
                    ],
                ],
            ],

            // 10. Deir ez-Zor
            'Deir ez-Zor' => [
                'ar' => 'دير الزور',
                'cities' => [
                    'Deir ez-Zor City' => [
                        'ar' => 'مدينة دير الزور',
                        'areas' => [
                            ['ar' => 'الجورة', 'en' => 'Al-Joura', 'lat' => 35.3333, 'lng' => 40.1400],
                            ['ar' => 'القصور', 'en' => 'Al-Qusour', 'lat' => 35.3400, 'lng' => 40.1450],
                        ],
                    ],
                ],
            ],

            // 11. Al-Hasakah
            'Al-Hasakah' => [
                'ar' => 'الحسكة',
                'cities' => [
                    'Al-Hasakah City' => [
                        'ar' => 'مدينة الحسكة',
                        'areas' => [
                            ['ar' => 'غويران', 'en' => 'Ghweran', 'lat' => 36.5000, 'lng' => 40.7500],
                            ['ar' => 'الناصرة', 'en' => 'Al-Nasira', 'lat' => 36.5050, 'lng' => 40.7550],
                        ],
                    ],
                ],
            ],

            // 12. Raqqa
            'Raqqa' => [
                'ar' => 'الرقة',
                'cities' => [
                    'Raqqa City' => [
                        'ar' => 'مدينة الرقة',
                        'areas' => [
                            ['ar' => 'الرشيد', 'en' => 'Al-Rashid', 'lat' => 35.9500, 'lng' => 39.0100],
                            ['ar' => 'الثكنة', 'en' => 'Al-Thakana', 'lat' => 35.9550, 'lng' => 39.0150],
                        ],
                    ],
                ],
            ],

            // 13. As-Suwayda
            'As-Suwayda' => [
                'ar' => 'السويداء',
                'cities' => [
                    'As-Suwayda City' => [
                        'ar' => 'مدينة السويداء',
                        'areas' => [
                            ['ar' => 'المدينة', 'en' => 'City Center', 'lat' => 32.7089, 'lng' => 36.5694],
                            ['ar' => 'الكرامة', 'en' => 'Al-Karama', 'lat' => 32.7100, 'lng' => 36.5700],
                        ],
                    ],
                ],
            ],

            // 14. Quneitra
            'Quneitra' => [
                'ar' => 'القنيطرة',
                'cities' => [
                    'Quneitra City' => [
                        'ar' => 'مدينة القنيطرة',
                        'areas' => [
                            ['ar' => 'المدينة', 'en' => 'City Center', 'lat' => 33.1256, 'lng' => 35.8244],
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
                            'en' => $area['en'] ?? $area['ar'],
                        ],
                        'lat' => $area['lat'] ?? null,
                        'lng' => $area['lng'] ?? null,
                        'city_id' => $city->id,
                        'base_fee' => 5
                    ]);
                }
            }
        }
    }
}
