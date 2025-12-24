<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        cache()->forget('active_locales');
        cache()->forget('languages_formatted');

        $languages = [
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'is_active' => true,
                'is_default' => true,
                'order' => 1,
                'flag_icon' => '🇸🇦',
                'locale' => 'ar_SA',
                'timezone' => 'Asia/Riyadh',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'decimal_separator' => '.',
                'thousands_separator' => ',',
                'currency_code' => 'SAR',
                'currency_symbol' => '﷼',
                'show_in_menu' => true,
                'show_in_switcher' => true,
                'og_locale' => 'ar_SA',
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => 2,
                'flag_icon' => '🇺🇸',
                'locale' => 'en_US',
                'timezone' => 'America/New_York',
                'date_format' => 'Y-m-d',
                'time_format' => 'h:i A',
                'decimal_separator' => '.',
                'thousands_separator' => ',',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'show_in_menu' => true,
                'show_in_switcher' => true,
                'og_locale' => 'en_US',
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => 3,
                'flag_icon' => '🇫🇷',
                'locale' => 'fr_FR',
                'timezone' => 'Europe/Paris',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'show_in_menu' => true,
                'show_in_switcher' => true,
                'og_locale' => 'fr_FR',
            ],
           
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        if (app()->environment('local')) {
            Language::factory()->count(5)->create();
        }
    }
}
