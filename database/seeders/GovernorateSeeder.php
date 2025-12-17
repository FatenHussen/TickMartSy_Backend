<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['ar' => 'دمشق',       'en' => 'Damascus'],
            ['ar' => 'ريف دمشق',   'en' => 'Rif Dimashq'],
            ['ar' => 'حلب',        'en' => 'Aleppo'],
            ['ar' => 'حمص',        'en' => 'Homs'],
            ['ar' => 'حماة',       'en' => 'Hama'],
            ['ar' => 'اللاذقية',   'en' => 'Latakia'],
            ['ar' => 'طرطوس',      'en' => 'Tartus'],
            ['ar' => 'إدلب',       'en' => 'Idlib'],
            ['ar' => 'دير الزور',  'en' => 'Deir ez-Zor'],
            ['ar' => 'الرقة',      'en' => 'Raqqa'],
            ['ar' => 'الحسكة',     'en' => 'Hasakah'],
            ['ar' => 'درعا',       'en' => 'Daraa'],
            ['ar' => 'السويداء',   'en' => 'As-Suwayda'],
            ['ar' => 'القنيطرة',   'en' => 'Quneitra'],
        ];

        foreach ($cities as $city) {
            DB::table('governorates')->insert([
                'name' => json_encode([
                    'ar' => $city['ar'],
                    'en' => $city['en'],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
