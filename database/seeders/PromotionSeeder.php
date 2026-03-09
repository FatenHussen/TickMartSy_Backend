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
            'name' => '10% Off Everything',
            'description' => 'Get 10% off on all products.',
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
            'name' => 'Spend 200 Get 20 Off',
            'description' => 'Get $20 off when spending $200 or more.',
            'type' => 'spend_x_discount',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'discount_value' => 20,
            'discount_type' => 'fixed',
            'min_spend' => 200,
            'buy_quantity' => null,
            'get_quantity' => null,
            'gift_product_ids' => [],
        ]);

        // Example: buy X get Y free promotion
        Promotion::create([
            'name' => 'Buy 2 Get 1 Free',
            'description' => 'Buy 2 products and get 1 free.',
            'type' => 'buy_x_get_y',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'discount_value' => 0,
            'discount_type' => null,
            'min_spend' => 0,
            'buy_quantity' => 2,
            'get_quantity' => 1,
            'gift_product_ids' => [1], // replace with real product IDs
        ]);
    }
}
