<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductMedia;

class ProductMediaSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        // gather all files stored under storage/app/public/product
        $files = Storage::disk('public')->files('product');

        foreach ($products as $product) {
            foreach ($files as $index => $file) {
                // $file already relative to public disk (e.g. product/image1.jpg)
                ProductMedia::create([
                    'mediable_id' => $product->id,
                    'mediable_type' => Product::class,
                    'collection' => 'product',
                    'path' => $file,
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
