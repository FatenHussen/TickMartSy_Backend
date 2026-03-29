<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\CategoryAttribute;

class CategoryAttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Get categories with their English names for easier mapping
        $electronics = Category::where('name->en', 'Electronics')->first();
        $fashion = Category::where('name->en', 'Fashion')->first();
        $rice = Category::where('name->en', 'Rice')->first();
        $shortGrainRice = Category::where('name->en', 'Short Grain Rice')->first();
        $longGrainRice = Category::where('name->en', 'Long Grain Rice')->first();
        $bulgur = Category::where('name->en', 'Bulgur')->first();
        $lentils = Category::where('name->en', 'Lentils')->first();

        // Electronics Attributes
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

        // Fashion Attributes
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

        // Rice Attributes (all rice types)
        foreach ([$rice, $shortGrainRice, $longGrainRice] as $riceCategory) {
            if ($riceCategory) {
                CategoryAttribute::create([
                    'category_id' => $riceCategory->id,
                    'name' => ['en' => 'Weight', 'ar' => 'الوزن'],
                    'type' => 'square'
                ]);

                CategoryAttribute::create([
                    'category_id' => $riceCategory->id,
                    'name' => ['en' => 'Quality', 'ar' => 'الجودة'],
                    'type' => 'circle'
                ]);
            }
        }

        // Bulgur Attributes
        if ($bulgur) {
            CategoryAttribute::create([
                'category_id' => $bulgur->id,
                'name' => ['en' => 'Weight', 'ar' => 'الوزن'],
                'type' => 'square'
            ]);

            CategoryAttribute::create([
                'category_id' => $bulgur->id,
                'name' => ['en' => 'Grain Size', 'ar' => 'حجم الحبة'],
                'type' => 'circle'
            ]);
        }

        // Lentils Attributes
        if ($lentils) {
            CategoryAttribute::create([
                'category_id' => $lentils->id,
                'name' => ['en' => 'Weight', 'ar' => 'الوزن'],
                'type' => 'square'
            ]);

            CategoryAttribute::create([
                'category_id' => $lentils->id,
                'name' => ['en' => 'Type', 'ar' => 'النوع'],
                'type' => 'circle'
            ]);
        }
    }
}
