<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: simple discount promotion
        Promotion::create([
            'name' => [
                'en' => '10% Off Everything',
                'ar' => 'خصم 10% على كل شيء'
            ],
            'description' => [
                'en' => 'Get 10% off on all products.',
                'ar' => 'احصل على خصم 10% على جميع المنتجات.'
            ],
            'type' => 'simple_discount',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'discount_value' => 10,
            'discount_type' => 'percentage',
            'min_spend' => 0,
            'buy_quantity' => null,
            'get_quantity' => null,
            'gift_product_ids' => [],
        ]);

        // Example: spend X discount
        Promotion::create([
            'name' => [
                'en' => 'Spend 100 Get 20 Off',
                'ar' => 'اصرف 100 واحصل على خصم 20'
            ],
            'description' => [
                'en' => 'Get $20 off when spending $200 or more.',
                'ar' => 'احصل على خصم 20 دولار عند الشراء بـ 200 دولار أو أكثر.'
            ],
            'type' => 'spend_x_discount',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'discount_value' => 20,
            'discount_type' => 'fixed',
            'min_spend' => 100,
            'buy_quantity' => null,
            'get_quantity' => null,
            'gift_product_ids' => [],
        ]);

        // Example: buy X get Y free promotion
        Promotion::create([
            'name' => [
                'en' => 'Buy 3 Get 1 Free',
                'ar' => 'اشتري 3 واحصل على 1 مجاناً'
            ],
            'description' => [
                'en' => 'Buy 3 products and get 1 free.',
                'ar' => 'اشتري 3 منتجات واحصل على واحد مجاناً.'
            ],
            'type' => 'buy_x_get_y',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'discount_value' => 0,
            'discount_type' => null,
            'min_spend' => 0,
            'buy_quantity' => 3,
            'get_quantity' => 1,
            'gift_product_ids' => [1], // replace with real product IDs
        ]);
    }
}
