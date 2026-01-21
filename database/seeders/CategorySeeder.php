<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{

    public function run(): void
    {
        $categories = [
            [
                'name' => ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
                'description' => ['en' => 'Devices and gadgets', 'ar' => 'أجهزة وإكسسوارات'],
                'icon' => 'categories/image1.jpg'
            ],
            [
                'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
                'description' => ['en' => 'Clothing and accessories', 'ar' => 'ملابس وإكسسوارات'],
                'icon' => 'categories/image2.jpg'
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $food = Category::create([
            'name' => [
                'ar' => 'مطاعم',
                'en' => 'Restaurants',
            ],
            'description' => [
                'ar' => 'مطاعم ومقاهي',
                'en' => 'Restaurants & Cafes',
            ],
        ]);

        Category::create([
            'name' => [
                'ar' => 'وجبات سريعة',
                'en' => 'Fast Food',
            ],
            'description' => [
                'ar' => 'مطاعم الوجبات السريعة',
                'en' => 'Fast food restaurants',
            ],
            'parent_id' => $food->id,
        ]);

        Category::create([
            'name' => [
                'ar' => 'حلويات',
                'en' => 'Desserts',
            ],
            'description' => [
                'ar' => 'محلات الحلويات',
                'en' => 'Dessert shops',
            ],
            'parent_id' => $food->id,
        ]);

        $main = Category::create([
            'name' => [
                'ar' => 'أكل',
                'en' => 'أكل'
            ],
            'description' => [
                'ar' => 'أكل',
                'en' => 'أكل'
            ]
        ]);

        $grains = Category::create([
            'name' => [
                'ar' => 'حبوب وبقوليات',
                'en' => 'Grains & Legumes'
            ],
            'parent_id' => $main->id
        ]);

        $rice = Category::create([
            'name' => [
                'ar' => 'رز',
                'en' => 'Rice'
            ],
            'parent_id' => $grains->id
        ]);

        Category::create([
            'name' => [
                'ar' => 'رز قصير',
                'en' => 'Short Grain Rice'
            ],
            'parent_id' => $rice->id
        ]);

        Category::create([
            'name' => [
                'ar' => 'رز طويل',
                'en' => 'Long Grain Rice'
            ],
            'parent_id' => $rice->id
        ]);

        Category::create([
            'name' => [
                'ar' => 'برغل',
                'en' => 'Bulgur'
            ],
            'parent_id' => $grains->id
        ]);

        Category::create([
            'name' => [
                'ar' => 'عدس',
                'en' => 'Lentils'
            ],
            'parent_id' => $grains->id
        ]);
    }
}
