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
        $products = Product::with('category')->get();
        $files = Storage::disk('public')->files('product');

        foreach ($products as $product) {
            // Get category attributes for this product's category
            $categoryAttributes = \App\Models\CategoryAttribute::where('category_id', $product->category_id)->get();

            if ($categoryAttributes->isEmpty()) {
                continue;
            }

            // Get attribute values for each attribute
            $attributeValuesByAttribute = [];
            foreach ($categoryAttributes as $attribute) {
                $values = \App\Models\AttributeValue::where('category_attribute_id', $attribute->id)
                    ->pluck('id')
                    ->toArray();
                if (!empty($values)) {
                    $attributeValuesByAttribute[$attribute->id] = $values;
                }
            }

            if (empty($attributeValuesByAttribute)) {
                continue;
            }

            // Create 4 variants with different combinations
            $combinations = $this->generateCombinations($attributeValuesByAttribute, 4);

            foreach ($combinations as $index => $combination) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'attributes_values_ids' => $combination,
                    'is_trend' => $index < 2 ? 1 : 0, // First 2 variants are trending
                ]);

                $this->addRandomImages($variant, $files);
            }
        }
    }

    private function generateCombinations(array $attributeValuesByAttribute, int $count): array
    {
        $combinations = [];
        $attributeIds = array_keys($attributeValuesByAttribute);

        // Generate random combinations
        for ($i = 0; $i < $count; $i++) {
            $combination = [];
            foreach ($attributeIds as $attributeId) {
                $values = $attributeValuesByAttribute[$attributeId];
                $combination[] = $values[array_rand($values)];
            }
            $combinations[] = $combination;
        }

        return $combinations;
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
