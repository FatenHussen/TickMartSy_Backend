<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductExtraDetail;
use App\Models\Category;

class ProductExtraDetailSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::limit(5)->get();

        foreach ($categories as $category) {
            ProductExtraDetail::create([
                'category_id' => $category->id,
                'detail_key' => ['en' => 'Material', 'ar' => 'الخامة'],
                'detail_value' => ['en' => 'Cotton', 'ar' => 'قطن'],
            ]);

            ProductExtraDetail::create([
                'category_id' => $category->id,
                'detail_key' => ['en' => 'Warranty', 'ar' => 'الضمان'],
                'detail_value' => ['en' => '2 Years', 'ar' => 'سنتين'],
            ]);
        }
    }
}
