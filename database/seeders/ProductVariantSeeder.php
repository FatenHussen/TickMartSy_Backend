<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            ProductVariant::create([
                'product_id' => $product->id,
                'attributes_values_ids' => [1, 5],
                'is_trend' => 1, // IDs من AttributeValue
            ]);
            ProductVariant::create([
                'product_id' => $product->id,
                'attributes_values_ids' => [2, 6],
                'is_trend' => 0, // IDs من AttributeValue

                 // IDs من AttributeValue
                 // IDs من AttributeValue
            ]);
        }
    }
}
