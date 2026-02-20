<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => [
                    'en' => 'US Dollar',
                    'ar' => 'دولار أمريكي'
                ],
                'symbol' => '$',
                'exchange_rate' => 1.00,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SYP',
                'name' => [
                    'en' => 'Syrian Pound',
                    'ar' => 'ليرة سورية'
                ],
                'symbol' => 'ل.س',
                'exchange_rate' => 13000.00, // مثال: 1 دولار = 13000 ليرة
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'AED',
                'name' => [
                    'en' => 'UAE Dirham',
                    'ar' => 'درهم إماراتي'
                ],
                'symbol' => 'د.إ',
                'exchange_rate' => 3.67, // 1 دولار = 3.67 درهم
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'SAR',
                'name' => [
                    'en' => 'Saudi Riyal',
                    'ar' => 'ريال سعودي'
                ],
                'symbol' => 'ر.س',
                'exchange_rate' => 3.75,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'EGP',
                'name' => [
                    'en' => 'Egyptian Pound',
                    'ar' => 'جنيه مصري'
                ],
                'symbol' => 'ج.م',
                'exchange_rate' => 30.90,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
