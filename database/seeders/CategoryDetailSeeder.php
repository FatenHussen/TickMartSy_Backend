<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\CategoryDetail;

class CategoryDetailSeeder extends Seeder
{
    public function run(): void
    {
        // Get specific categories
        $electronics = Category::where('name->en', 'Electronics')->first();
        $fashion = Category::where('name->en', 'Fashion')->first();
        $rice = Category::where('name->en', 'Rice')->first();
        $shortGrainRice = Category::where('name->en', 'Short Grain Rice')->first();
        $longGrainRice = Category::where('name->en', 'Long Grain Rice')->first();
        $bulgur = Category::where('name->en', 'Bulgur')->first();
        $lentils = Category::where('name->en', 'Lentils')->first();

        // Electronics Details
        if ($electronics) {
            CategoryDetail::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'Processor', 'ar' => 'المعالج'],
            ]);
            CategoryDetail::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'Screen Size', 'ar' => 'حجم الشاشة'],
            ]);
            CategoryDetail::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'Battery', 'ar' => 'البطارية'],
            ]);
            CategoryDetail::create([
                'category_id' => $electronics->id,
                'name' => ['en' => 'Warranty', 'ar' => 'الضمان'],
            ]);
        }

        // Fashion Details
        if ($fashion) {
            CategoryDetail::create([
                'category_id' => $fashion->id,
                'name' => ['en' => 'Material', 'ar' => 'الخامة'],
            ]);
            CategoryDetail::create([
                'category_id' => $fashion->id,
                'name' => ['en' => 'Care Instructions', 'ar' => 'تعليمات العناية'],
            ]);
            CategoryDetail::create([
                'category_id' => $fashion->id,
                'name' => ['en' => 'Country of Origin', 'ar' => 'بلد المنشأ'],
            ]);
        }

        // Rice Details (all types)
        foreach ([$rice, $shortGrainRice, $longGrainRice] as $riceCategory) {
            if ($riceCategory) {
                CategoryDetail::create([
                    'category_id' => $riceCategory->id,
                    'name' => ['en' => 'Grain Length', 'ar' => 'طول الحبة'],
                ]);
                CategoryDetail::create([
                    'category_id' => $riceCategory->id,
                    'name' => ['en' => 'Cooking Time', 'ar' => 'وقت الطبخ'],
                ]);
                CategoryDetail::create([
                    'category_id' => $riceCategory->id,
                    'name' => ['en' => 'Best For', 'ar' => 'الأفضل لـ'],
                ]);
                CategoryDetail::create([
                    'category_id' => $riceCategory->id,
                    'name' => ['en' => 'Storage', 'ar' => 'التخزين'],
                ]);
            }
        }

        // Bulgur Details
        if ($bulgur) {
            CategoryDetail::create([
                'category_id' => $bulgur->id,
                'name' => ['en' => 'Grain Size', 'ar' => 'حجم الحبة'],
            ]);
            CategoryDetail::create([
                'category_id' => $bulgur->id,
                'name' => ['en' => 'Preparation Time', 'ar' => 'وقت التحضير'],
            ]);
            CategoryDetail::create([
                'category_id' => $bulgur->id,
                'name' => ['en' => 'Nutritional Value', 'ar' => 'القيمة الغذائية'],
            ]);
        }

        // Lentils Details
        if ($lentils) {
            CategoryDetail::create([
                'category_id' => $lentils->id,
                'name' => ['en' => 'Type', 'ar' => 'النوع'],
            ]);
            CategoryDetail::create([
                'category_id' => $lentils->id,
                'name' => ['en' => 'Cooking Time', 'ar' => 'وقت الطبخ'],
            ]);
            CategoryDetail::create([
                'category_id' => $lentils->id,
                'name' => ['en' => 'Protein Content', 'ar' => 'محتوى البروتين'],
            ]);
        }
    }
}
