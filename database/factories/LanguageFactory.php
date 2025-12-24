<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = \App\Models\Language::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->languageCode,
            'name' => $this->faker->country,
            'native_name' => $this->faker->country,
            'direction' => $this->faker->randomElement(['ltr', 'rtl']),
            'is_active' => $this->faker->boolean(90),
            'is_default' => false,
            'order' => $this->faker->numberBetween(1, 100),
            'flag_icon' => $this->faker->randomElement(['🇸🇦', '🇺🇸', '🇬🇧', '🇫🇷', '🇩🇪', null]),
            'locale' => $this->faker->locale,
            'timezone' => $this->faker->timezone,
            'date_format' => $this->faker->randomElement(['Y-m-d', 'd/m/Y', 'm/d/Y']),
            'time_format' => $this->faker->randomElement(['H:i', 'h:i A']),
            'decimal_separator' => $this->faker->randomElement(['.', ',']),
            'thousands_separator' => $this->faker->randomElement([',', '.', ' ']),
            'currency_code' => $this->faker->currencyCode,
            'currency_symbol' => $this->faker->currencySymbol,
            'show_in_menu' => $this->faker->boolean(80),
            'show_in_switcher' => $this->faker->boolean(80),
            'og_locale' => $this->faker->locale,
        ];
    }

    public function arabic()
    {
        return $this->state([
            'code' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'is_default' => true,
            'order' => 1,
            'flag_icon' => '🇸🇦',
            'locale' => 'ar_SA',
            'timezone' => 'Asia/Riyadh',
            'currency_code' => 'SAR',
            'currency_symbol' => '﷼',
        ]);
    }

    public function english()
    {
        return $this->state([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'order' => 2,
            'flag_icon' => '🇺🇸',
            'locale' => 'en_US',
            'timezone' => 'America/New_York',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
        ]);
    }

    public function french()
    {
        return $this->state([
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'direction' => 'ltr',
            'order' => 3,
            'flag_icon' => '🇫🇷',
            'locale' => 'fr_FR',
            'timezone' => 'Europe/Paris',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
        ]);
    }

    public function inactive()
    {
        return $this->state([
            'is_active' => false,
            'show_in_menu' => false,
            'show_in_switcher' => false,
        ]);
    }
}
