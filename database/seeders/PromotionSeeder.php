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
        ]);

        // Example: spend X grant gift
        Promotion::create([
            'name' => [
                'en' => 'Spend 200 Get a Gift',
                'ar' => 'أنفق 200 و احصل على هدية'
            ],
            'description' => [
                'en' => 'Receive a curated gift when you spend 200 or more.',
                'ar' => 'احصل على هدية مختارة عند الإنفاق 200 أو أكثر.'
            ],
            'type' => 'spend_x_get_gift',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'min_spend' => 200,
            'gift_description' => [
                'en' => 'A surprise gift from the store (not tied to a catalog product).',
                'ar' => 'هدية مفاجئة من المتجر (غير مرتبطة بمنتج محدد).',
            ],
            'reward_points' => null,
            'discount_value' => null,
            'discount_type' => null,
        ]);

        // Example: spend X get points
        Promotion::create([
            'name' => [
                'en' => 'Spend 150 to earn 50 points',
                'ar' => 'أنفق 150 لتحصل على 50 نقطة'
            ],
            'description' => [
                'en' => 'Earn 50 loyalty points when you spend 150 or more.',
                'ar' => 'احصل على 50 نقطة ولاء عند الإنفاق 150 أو أكثر.'
            ],
            'type' => 'spend_x_get_points',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'min_spend' => 150,
            'reward_points' => 50,
            'discount_value' => null,
            'discount_type' => null,
        ]);

        // Example: unconditional free shipping
        Promotion::create([
            'name' => [
                'en' => 'Free Shipping',
                'ar' => 'توصيل مجاني'
            ],
            'description' => [
                'en' => 'Free delivery on your order — no minimum spend.',
                'ar' => 'توصيل مجاني على طلبك دون حد أدنى للشراء.'
            ],
            'type' => 'free_shipping',
            'is_active' => true,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(30),
            'min_spend' => null,
            'reward_points' => null,
            'discount_value' => null,
            'discount_type' => null,
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
        ]);
    }
}
