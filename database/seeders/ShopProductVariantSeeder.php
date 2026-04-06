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
            $basePrice = $variant->product->price ?? 100;

            // Assign variant to 1-3 random shops (not all shops)
            $assignedShops = $shops->random(min(rand(1, 3), $shops->count()));

            foreach ($assignedShops as $shop) {
                // Skip if already exists
                if (ShopProductVariant::where('product_variant_id', $variant->id)
                    ->where('shop_id', $shop->id)->exists()) {
                    continue;
                }

                // Price variation ±15%
                $variation = rand(-15, 15);
                $shopPrice = round($basePrice + ($basePrice * $variation / 100));

                ShopProductVariant::create([
                    'product_variant_id' => $variant->id,
                    'shop_id'            => $shop->id,
                    'quantity'           => rand(10, 200),
                    'price'              => max(1, $shopPrice),
                ]);
            }
        }
    }
}
