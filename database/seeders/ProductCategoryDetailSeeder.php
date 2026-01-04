<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\CategoryDetail;
use App\Models\ProductCategoryDetail;

class ProductCategoryDetailSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            $details = CategoryDetail::where('category_id', $product->category_id)->get();

            foreach ($details as $detail) {
                ProductCategoryDetail::create([
                    'product_id' => $product->id,
                    'category_detail_id' => $detail->id,
                    'detail_value' => ['en' => 'Example value', 'ar' => 'قيمة مثال'],
                ]);
            }
        }
    }
}
