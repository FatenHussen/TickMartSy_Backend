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
                'icon' => '📱'
            ],
            [
                'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
                'description' => ['en' => 'Clothing and accessories', 'ar' => 'ملابس وإكسسوارات'],
                'icon' => '👗'
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
