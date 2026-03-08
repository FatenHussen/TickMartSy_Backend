<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => ['en' => 'Sample Product', 'ar' => 'منتج تجريبي'],
                'description' => ['en' => 'Short description', 'ar' => 'وصف قصير'],
                'full_description' => ['en' => 'Full product description', 'ar' => 'وصف كامل للمنتج'],
                'sku' => 'SKU-' . rand(1000, 9999),
                'country' => ['en' => 'USA', 'ar' => 'أمريكا'],
                'model' => 'Model-' . rand(100, 999),
                'price' => 100,
                'discount' => 20,
                'quantity' => 50,
                'barcode' => 'BAR' . rand(1000, 9999),
                'time_prepare' => now()->format('H:i'),
                'bought_with' => [1,2],
                'is_instant_delivery' => true,
                'brand_id' => 1
            ]);

            $product->badges()->attach([
                1 => ['position' => 'top'],
                2 => ['position' => 'bottom'],
                3 => ['position' => 'bottom'],
            ]);
        }
    }
}
