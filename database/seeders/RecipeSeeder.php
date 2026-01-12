<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    public function run()
    {
        $resCategoryId = DB::table('categories')->insertGetId([
            'name' => json_encode(['ar' => 'رز قصير أبيض', 'en' => 'Short White Rice']),
            'parent_id' => DB::table('categories')->insertGetId([
                'name' => json_encode(['ar' => 'رز قصير', 'en' => 'Short Rice']),
                'parent_id' => DB::table('categories')->insertGetId([
                    'name' => json_encode(['ar' => 'رز و برغل', 'en' => 'Rice & Bulgur']),
                    'parent_id' => DB::table('categories')->insertGetId([
                        'name' => json_encode(['ar' => 'أكل', 'en' => 'Food'])
                    ])
                ])
            ])
        ]);

        $vendorId = DB::table('vendors')->insertGetId([
            'name' => json_encode(['ar' => 'تاجر الكبسة', 'en' => 'Kabsa Vendor']),
            'owner_name' => 'أحمد',
            'owner_phone' => '0999999999',
            'contract_date' => now(),
            'contract_number' => 'KABSA001',
            'contract_duration_months' => 12,
            'commission_rate' => 5.00,
            'is_active' => true,
        ]);

        $shopId = DB::table('shops')->insertGetId([
            'name' => json_encode(['ar' => 'فرع المزة', 'en' => 'Al-Mazza Branch']),
            'mobile' => '0998888888',
            'email' => 'mazza@kabsa.com',
            'area_id' => 1,
            'address' => json_encode(['ar' => ' المزة', 'en' => 'Al-Mazza ']),
            'vendor_id' => $vendorId,
            'working_hours' => json_encode(['sat-sun' => '08:00-20:00']),
            'is_active' => true,
        ]);

        $productId = DB::table('products')->insertGetId([
            'category_id' => $resCategoryId,
            'vendor_id' => $vendorId,
            'name' => json_encode(['ar' => 'رز قصير أبيض', 'en' => 'Short White Rice']),
            'description' => json_encode(['ar' => 'رز ممتاز', 'en' => 'Premium Rice']),
            'price' => 2000,
        ]);

        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId,
            'attributes_values_ids' => json_encode([1]) // مثال الوزن
        ]);

        DB::table('shop_product_variants')->insert([
            'product_variant_id' => $variantId,
            'shop_id' => $shopId,
            'quantity' => 100,
            'price' => 2000,
        ]);

        $recipeId = DB::table('recipes')->insertGetId([
            'name' => json_encode(['ar' => 'كبسة رز', 'en' => 'Kabsa Rice']),
            'description' => json_encode(['ar' => 'كبسة رز بالدجاج', 'en' => 'Rice Kabsa with Chicken']),
            'image' => 'recipes/kabsa.jpg',
            'discount' => 0,
            'rating' => 4.5,
            'orders_count' => 0,
            'is_active' => true,
        ]);

        DB::table('recipe_items')->insert([
            [
                'recipe_id' => $recipeId,
                'shop_product_variant_id' => $variantId, // رز قصير أبيض
                'quantity' => 1,
                'switchable_category_level' => 4,
                'is_required' => true,
                'min_quantity' => 1,
                'max_quantity' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'recipe_id' => $recipeId,
                'shop_product_variant_id' => 2, // سمنة افتراضية
                'quantity' => 1,
                'switchable_category_level' => 3,
                'is_required' => true,
                'min_quantity' => 1,
                'max_quantity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'recipe_id' => $recipeId,
                'shop_product_variant_id' => 3,
                'quantity' => 2,
                'switchable_category_level' => 4,
                'is_required' => false,
                'min_quantity' => 0,
                'max_quantity' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
