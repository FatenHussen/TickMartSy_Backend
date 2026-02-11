<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductMedia;

class ProductMediaSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // صور للمنتج نفسه
            ProductMedia::create([
                'mediable_id' => $product->id,
                'mediable_type' => Product::class,
                'collection' => 'product',
                'path' => 'product/image1.jpg',
                'order' => 1,
            ]);
        }
    }
}
