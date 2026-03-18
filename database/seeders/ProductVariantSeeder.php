<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductMedia;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        // Get all images from storage
        $files = Storage::disk('public')->files('product');

        foreach ($products as $product) {
            $variant1 = ProductVariant::create([
                'product_id' => $product->id,
                'attributes_values_ids' => [1, 5],
                'is_trend' => 1,
            ]);

            $variant2 = ProductVariant::create([
                'product_id' => $product->id,
                'attributes_values_ids' => [2, 6],
                'is_trend' => 0,
            ]);

            // Add 4 random images for each variant
            $this->addRandomImages($variant1, $files);
            $this->addRandomImages($variant2, $files);
        }
    }

    private function addRandomImages(ProductVariant $variant, array $files): void
    {
        if (empty($files)) {
            return;
        }

        // Get 4 random images
        $randomFiles = collect($files)->shuffle()->take(4);

        foreach ($randomFiles as $index => $file) {
            ProductMedia::create([
                'mediable_id' => $variant->id,
                'mediable_type' => ProductVariant::class,
                'collection' => ProductMedia::COLLECTION_VARIANT,
                'path' => $file,
                'order' => $index + 1,
            ]);
        }
    }
}
