<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\CategoryAttribute;

class CategoryAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            CategoryAttribute::create([
                'category_id' => $category->id,
                'name' => [
                    'en' => 'Color',
                    'ar' => 'اللون',
                ],
                'type' => 'color'
            ]);

            CategoryAttribute::create([
                'category_id' => $category->id,
                'name' => [
                    'en' => 'Size',
                    'ar' => 'المقاس',
                ],
                'type' => 'square'
            ]);
        }
    }
}
