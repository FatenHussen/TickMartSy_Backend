<?php

namespace Database\Seeders;

use App\Models\UserBasketSchedule;
use App\Models\Product;
use App\Models\ShopProductVariant;
use App\Models\UserBasketScheduleItem;
use Illuminate\Database\Seeder;

class UserBasketScheduleItemSeeder extends Seeder
{
    public function run(): void
    {
        $basket = UserBasketSchedule::first();
        if (!$basket) {
            return;
        }

        $products = Product::where('category_id', $basket->category_id)->take(3)->get();

        foreach ($products as $product) {
            $variant = ShopProductVariant::whereHas('productVariant', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->first();

            UserBasketScheduleItem::create([
                'user_basket_schedule_id' => $basket->id,
                'product_id' => $product->id,
                'shop_product_variant_id' => $variant?->id,
                'quantity' => rand(1, 3),
            ]);
        }
    }
}
