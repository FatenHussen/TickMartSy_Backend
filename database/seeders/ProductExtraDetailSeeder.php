<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductExtraDetail;

class ProductExtraDetailSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            ProductExtraDetail::create([
                'product_id' => $product->id,
                'detail_key' => ['en' => 'Material', 'ar' => 'الخامة'],
                'detail_value' => ['en' => 'Cotton', 'ar' => 'قطن'],
            ]);

            ProductExtraDetail::create([
                'product_id' => $product->id,
                'detail_key' => ['en' => 'Warranty', 'ar' => 'الضمان'],
                'detail_value' => ['en' => '2 Years', 'ar' => 'سنتين'],
            ]);
        }
    }
}
