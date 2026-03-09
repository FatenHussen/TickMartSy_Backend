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

        // get all images
        $files = Storage::disk('public')->files('product');

        foreach ($products as $product) {

            // randomize images
            $randomFiles = collect($files)->shuffle()->take(rand(2, 5));

            foreach ($randomFiles as $index => $file) {
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
