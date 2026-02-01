<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Coupon 1: عام
        Coupon::create([
            'name' => ['en' => 'Welcome Discount', 'ar' => 'خصم ترحيبي'],
            'code'           => 'WELCOME10',
            'discount_type'  => 'percentage',
            'discount_value' => 10,
            'start_at'       => Carbon::now()->subDay(),
            'end_at'         => Carbon::now()->addMonth(),
            'max_uses'       => 100,
            'used_count'     => 0,
            'is_active'      => true,
        ]);

        // Coupon 2: مربوط بمنتجات
        $productCoupon = Coupon::create([
            'name' => ['en' => 'Welcome Discount', 'ar' => 'خصم ترحيبي'],

            'code'           => 'PROD20',
            'discount_type'  => 'percentage',
            'discount_value' => 20,
            'start_at'       => now(),
            'end_at'         => now()->addMonth(),
            'max_uses'       => 50,
            'used_count'     => 0,
            'is_active'      => true,
        ]);

        $products = Product::inRandomOrder()->limit(3)->pluck('id');
        $productCoupon->products()->sync($products);

        // Coupon 3: مربوط بفئات
        $categoryCoupon = Coupon::create([
            'name' => ['en' => 'Welcome Discount', 'ar' => 'خصم ترحيبي'],

            'code'           => 'CAT15',
            'discount_type'  => 'fixed',
            'discount_value' => 15000,
            'start_at'       => now(),
            'end_at'         => now()->addWeeks(2),
            'max_uses'       => 30,
            'used_count'     => 0,
            'is_active'      => true,
        ]);

        $categories = Category::inRandomOrder()->limit(2)->pluck('id');
        $categoryCoupon->categories()->sync($categories);

        // Coupon 4: مربوط ببائع
        $vendorCoupon = Coupon::create([
            'name' => ['en' => 'Welcome Discount', 'ar' => 'خصم ترحيبي'],

            'code'           => 'VENDOR25',
            'discount_type'  => 'percentage',
            'discount_value' => 25,
            'start_at'       => now(),
            'end_at'         => now()->addMonth(),
            'max_uses'       => 20,
            'used_count'     => 0,
            'is_active'      => true,
        ]);

        $vendor = Vendor::inRandomOrder()->first();
        if ($vendor) {
            $vendorCoupon->vendors()->sync([$vendor->id]);
        }
    }
}
