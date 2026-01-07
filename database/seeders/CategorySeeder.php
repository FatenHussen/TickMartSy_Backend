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
                'icon' => 'https://tikmool.octopus-software.online/storage/banner/9a37073f-39cc-4860-9366-dd9483112ab0.jpg'
            ],
            [
                'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
                'description' => ['en' => 'Clothing and accessories', 'ar' => 'ملابس وإكسسوارات'],
                'icon' => 'https://tikmool.octopus-software.online/storage/banner/9a37073f-39cc-4860-9366-dd9483112ab0.jpg'
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
    }
}
