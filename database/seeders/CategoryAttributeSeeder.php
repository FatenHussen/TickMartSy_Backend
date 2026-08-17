<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\CategoryAttribute;

class CategoryAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('name->en', 'Electronics')->first();
        $fashion = Category::where('name->en', 'Fashion')->first();
        $food = Category::where('name->en', 'Food')->first();

        if ($electronics) {
            CategoryAttribute::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'Color', 'ar' => 'اللون'],
                'type' => 'color'
            ]);

            CategoryAttribute::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'Storage', 'ar' => 'السعة التخزينية'],
                'type' => 'square'
            ]);

            CategoryAttribute::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'RAM', 'ar' => 'الذاكرة العشوائية'],
                'type' => 'square'
            ]);
        }

        if ($fashion) {
            CategoryAttribute::create([
                'category_id' => $fashion->id,
                'name' => ['en' => 'Color', 'ar' => 'اللون'],
                'type' => 'color'
            ]);

            CategoryAttribute::create([
                'category_id' => $fashion->id,
                'name' => ['en' => 'Size', 'ar' => 'المقاس'],
                'type' => 'square'
            ]);

            CategoryAttribute::create([
                'category_id' => $fashion->id,
                'name' => ['en' => 'Material', 'ar' => 'المادة'],
                'type' => 'circle'
            ]);
        }

        if ($food) {
            CategoryAttribute::create([
                'category_id' => $food->id,
                'name' => ['en' => 'Weight', 'ar' => 'الوزن'],
                'type' => 'square'
            ]);

            CategoryAttribute::create([
                'category_id' => $food->id,
                'name' => ['en' => 'Quality', 'ar' => 'الجودة'],
                'type' => 'circle'
            ]);
        }
    }
}
