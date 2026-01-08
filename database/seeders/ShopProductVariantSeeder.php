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
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            foreach ($shops as $shop) {
                ShopProductVariant::create([
                    'product_variant_id' => $variant->id,
                    'shop_id' => $shop->id,
                    'quantity' => rand(10, 100), 
                    'price' => rand(50, 200),   
                ]);
            }
        }
    }
}
