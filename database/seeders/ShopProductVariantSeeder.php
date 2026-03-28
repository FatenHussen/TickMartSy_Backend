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
        $variants = ProductVariant::with('product')->get();

        foreach ($variants as $variant) {
            $basePrice = $variant->product->price ?? 100;

            foreach ($shops as $shop) {
                // Each shop has slightly different price (±20%)
                $priceVariation = rand(-20, 20);
                $shopPrice = $basePrice + ($basePrice * $priceVariation / 100);

                // Random discount (0-30%)
                $discount = rand(0, 30);
                $finalPrice = $shopPrice - ($shopPrice * $discount / 100);

                ShopProductVariant::create([
                    'product_variant_id' => $variant->id,
                    'shop_id' => $shop->id,
                    'quantity' => rand(10, 100),
                    'price' => round($shopPrice),
                ]);
            }
        }
    }
}
