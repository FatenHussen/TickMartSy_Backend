<?php

namespace Database\Seeders;

use App\Models\SaleCountry;
use Illuminate\Database\Seeder;

class SaleCountrySeeder extends Seeder
{
    public function run(): void
    {
        $saleCountries = [
            [
                'name' => [
                    'ar' => 'السعودية',
                    'en' => 'Saudi Arabia',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'ar' => 'الإمارات',
                    'en' => 'UAE',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'ar' => 'الكويت',
                    'en' => 'Kuwait',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'ar' => 'قطر',
                    'en' => 'Qatar',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'ar' => 'البحرين',
                    'en' => 'Bahrain',
                ],
                'is_active' => true,
            ],
            [
                'name' => [
                    'ar' => 'عمان',
                    'en' => 'Oman',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($saleCountries as $country) {
            SaleCountry::create($country);
        }
    }
}
