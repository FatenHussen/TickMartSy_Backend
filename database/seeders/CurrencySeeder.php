<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * exchange_rate = كم ليرة سورية تساوي 1 وحدة من هذه العملة
     * الليرة السورية هي العملة الأساسية (is_default = true, exchange_rate = 1)
     *
     * مثال التحويل:
     *   سعر بالليرة × exchange_rate = سعر بالعملة الأخرى
     *   سعر بالعملة الأخرى ÷ exchange_rate = سعر بالليرة
     */
    public function run(): void
    {
        $currencies = [
            [
                'code'          => 'SYP',
                'name'          => ['en' => 'Syrian Pound', 'ar' => 'ليرة سورية'],
                'symbol'        => 'ل.س',
                'exchange_rate' => 1.000000,   // العملة الأساسية
                'is_default'    => true,
                'is_active'     => true,
            ],
            [
                'code'          => 'USD',
                'name'          => ['en' => 'US Dollar', 'ar' => 'دولار أمريكي'],
                'symbol'        => '$',
                'exchange_rate' => 0.0077,   // 1 ليرة = 0.000077 دولار (1 دولار ≈ 13000 ليرة)
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'AED',
                'name'          => ['en' => 'UAE Dirham', 'ar' => 'درهم إماراتي'],
                'symbol'        => 'د.إ',
                'exchange_rate' => 0.0283,   // 1 ليرة ≈ 0.000283 درهم (1 درهم ≈ 3540 ليرة)
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'SAR',
                'name'          => ['en' => 'Saudi Riyal', 'ar' => 'ريال سعودي'],
                'symbol'        => 'ر.س',
                'exchange_rate' => 0.0288,   // 1 ليرة ≈ 0.000288 ريال (1 ريال ≈ 3467 ليرة)
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'EGP',
                'name'          => ['en' => 'Egyptian Pound', 'ar' => 'جنيه مصري'],
                'symbol'        => 'ج.م',
                'exchange_rate' => 0.2375,   // 1 ليرة ≈ 0.002375 جنيه (1 جنيه ≈ 421 ليرة)
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'code'          => 'TRY',
                'name'          => ['en' => 'Turkish Lira', 'ar' => 'ليرة تركية'],
                'symbol'        => '₺',
                'exchange_rate' => 0.2500,   // 1 ليرة سورية ≈ 0.0025 ليرة تركية (1 تركية ≈ 400 سورية)
                'is_default'    => false,
                'is_active'     => true,
            ],
        ];

        // Reset all defaults first
        Currency::where('is_default', true)->update(['is_default' => false]);

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
