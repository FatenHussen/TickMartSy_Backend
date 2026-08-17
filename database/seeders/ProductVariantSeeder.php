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
            $categoryAttributes = \App\Models\CategoryAttribute::query()
                ->forCategoryTree($product->category_id)
                ->get();

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
                // Build variant name from attribute values
                $nameAr = [];
                $nameEn = [];
                foreach ($combination as $valueId) {
                    $attrValue = \App\Models\AttributeValue::find($valueId);
                    if ($attrValue) {
                        $nameAr[] = $attrValue->getTranslation('name', 'ar', false) ?? '';
                        $nameEn[] = $attrValue->getTranslation('name', 'en', false) ?? '';
                    }
                }

                $variant = ProductVariant::create([
                    'product_id'            => $product->id,
                    'name'                  => [
                        'ar' => implode(' - ', array_filter($nameAr)) ?: null,
                        'en' => implode(' - ', array_filter($nameEn)) ?: null,
                    ],
                    'sku'                   => 'VAR-' . $product->id . '-' . ($index + 1) . '-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                    'price'                 => $product->price ?? 100,
                    'quantity'              => $product->quantity ?? rand(20, 150),
                    'attributes_values_ids' => $combination,
                    'is_trend'              => $index < 2 ? 1 : 0,
                    'is_active'             => true,
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
