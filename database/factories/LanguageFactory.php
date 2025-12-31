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
            'native_name' => $this->faker->country,
            'direction' => $this->faker->randomElement(['ltr', 'rtl']),
            'is_active' => $this->faker->boolean(90),
            'is_default' => false,
            'order' => $this->faker->numberBetween(1, 100),
            'flag_icon' => $this->faker->randomElement(['🇸🇦', '🇺🇸', '🇬🇧', '🇫🇷', '🇩🇪', null]),
        ];
    }

    public function arabic()
    {
        return $this->state([
            'code' => 'ar',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'is_default' => true,
            'order' => 1,
            'flag_icon' => '🇸🇦',
            
        ]);
    }

    public function english()
    {
        return $this->state([
            'code' => 'en',
            'native_name' => 'English',
            'direction' => 'ltr',
            'order' => 2,
            'flag_icon' => '🇺🇸',
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
