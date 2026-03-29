<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => ['en' => 'Red', 'ar' => 'أحمر'], 'hex' => '#FF0000'],
            ['name' => ['en' => 'Blue', 'ar' => 'أزرق'], 'hex' => '#0000FF'],
            ['name' => ['en' => 'Green', 'ar' => 'أخضر'], 'hex' => '#00FF00'],
            ['name' => ['en' => 'Yellow', 'ar' => 'أصفر'], 'hex' => '#FFFF00'],
            ['name' => ['en' => 'Orange', 'ar' => 'برتقالي'], 'hex' => '#FFA500'],
            ['name' => ['en' => 'Purple', 'ar' => 'بنفسجي'], 'hex' => '#800080'],
            ['name' => ['en' => 'Pink', 'ar' => 'وردي'], 'hex' => '#FFC0CB'],
            ['name' => ['en' => 'Brown', 'ar' => 'بني'], 'hex' => '#A52A2A'],
            ['name' => ['en' => 'Black', 'ar' => 'أسود'], 'hex' => '#000000'],
            ['name' => ['en' => 'White', 'ar' => 'أبيض'], 'hex' => '#FFFFFF'],
            ['name' => ['en' => 'Gray', 'ar' => 'رمادي'], 'hex' => '#808080'],
            ['name' => ['en' => 'Silver', 'ar' => 'فضي'], 'hex' => '#C0C0C0'],
            ['name' => ['en' => 'Gold', 'ar' => 'ذهبي'], 'hex' => '#FFD700'],
            ['name' => ['en' => 'Beige', 'ar' => 'بيج'], 'hex' => '#F5F5DC'],
            ['name' => ['en' => 'Navy', 'ar' => 'كحلي'], 'hex' => '#000080'],
            ['name' => ['en' => 'Turquoise', 'ar' => 'تركواز'], 'hex' => '#40E0D0'],
            ['name' => ['en' => 'Maroon', 'ar' => 'عنابي'], 'hex' => '#800000'],
            ['name' => ['en' => 'Olive', 'ar' => 'زيتوني'], 'hex' => '#808000'],
            ['name' => ['en' => 'Lime', 'ar' => 'ليموني'], 'hex' => '#00FF00'],
            ['name' => ['en' => 'Cyan', 'ar' => 'سماوي'], 'hex' => '#00FFFF'],
            ['name' => ['en' => 'Magenta', 'ar' => 'أرجواني'], 'hex' => '#FF00FF'],
            ['name' => ['en' => 'Coral', 'ar' => 'مرجاني'], 'hex' => '#FF7F50'],
            ['name' => ['en' => 'Salmon', 'ar' => 'سلموني'], 'hex' => '#FA8072'],
            ['name' => ['en' => 'Khaki', 'ar' => 'كاكي'], 'hex' => '#F0E68C'],
            ['name' => ['en' => 'Violet', 'ar' => 'بنفسجي فاتح'], 'hex' => '#EE82EE'],
            ['name' => ['en' => 'Indigo', 'ar' => 'نيلي'], 'hex' => '#4B0082'],
            ['name' => ['en' => 'Crimson', 'ar' => 'قرمزي'], 'hex' => '#DC143C'],
            ['name' => ['en' => 'Teal', 'ar' => 'أزرق مخضر'], 'hex' => '#008080'],
            ['name' => ['en' => 'Lavender', 'ar' => 'لافندر'], 'hex' => '#E6E6FA'],
            ['name' => ['en' => 'Mint', 'ar' => 'نعناعي'], 'hex' => '#98FF98'],
        ];

        foreach ($colors as $color) {
            Color::create($color);
        }
    }
}
