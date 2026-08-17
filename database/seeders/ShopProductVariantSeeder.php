<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\ProductVariant;
use App\Models\ShopProductVariant;

class ShopProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::all();

        if ($shops->isEmpty()) {
            return;
        }

        $variants = ProductVariant::with('product')->get();

        foreach ($variants as $variant) {
            // السعر صار على المتغير نفسه، المحل بيحمل التكلفة والكمية بس
            $basePrice = $variant->price ?? $variant->product->price ?? 100;

            // Assign variant to 1-3 random shops (not all shops)
            $assignedShops = $shops->random(min(rand(1, 3), $shops->count()));

            foreach ($assignedShops as $shop) {
                // Skip if already exists
                if (ShopProductVariant::where('product_variant_id', $variant->id)
                    ->where('shop_id', $shop->id)->exists()) {
                    continue;
                }

                ShopProductVariant::create([
                    'product_variant_id' => $variant->id,
                    'shop_id'            => $shop->id,
                    'cost_price'         => max(1, (int) round($basePrice * 0.75)),
                ]);
            }
        }
    }
}
