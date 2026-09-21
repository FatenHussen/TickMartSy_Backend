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
            ProductExtraDetail::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'detail_key' => ['en' => 'Gift Wrapping', 'ar' => 'تغليف هدايا'],
                ],
                [
                    'detail_value' => ['en' => 'Premium wrap', 'ar' => 'تغليف فاخر'],
                    'price' => 50,
                    'is_active' => true,
                ]
            );

            ProductExtraDetail::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'detail_key' => ['en' => 'Extended Warranty', 'ar' => 'ضمان إضافي'],
                ],
                [
                    'detail_value' => ['en' => 'One extra year', 'ar' => 'سنة إضافية'],
                    'price' => 100,
                    'is_active' => true,
                ]
            );
        }
    }
}
