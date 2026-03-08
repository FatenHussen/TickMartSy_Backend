<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Vendor;
use App\Models\Shop;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShopProductVariant;
use App\Models\ProductMedia;
use App\Models\Recipe;
use App\Models\RecipeItem;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // all product images
        $files = Storage::disk('public')->files('product');

        /* =======================
         * Categories
         * ======================= */

        $food = Category::create([
            'name' => ['ar' => 'أكل', 'en' => 'Food'],
        ]);

        $riceBulgur = Category::create([
            'name' => ['ar' => 'رز و برغل', 'en' => 'Rice & Bulgur'],
            'parent_id' => $food->id,
        ]);

        $shortRice = Category::create([
            'name' => ['ar' => 'رز قصير', 'en' => 'Short Rice'],
            'parent_id' => $riceBulgur->id,
        ]);

        $shortWhiteRice = Category::create([
            'name' => ['ar' => 'رز قصير أبيض', 'en' => 'Short White Rice'],
            'parent_id' => $shortRice->id,
        ]);

        $longRice = Category::create([
            'name' => ['ar' => 'رز طويل', 'en' => 'Long Rice'],
            'parent_id' => $riceBulgur->id,
        ]);

        $longWhiteRice = Category::create([
            'name' => ['ar' => 'رز طويل أبيض', 'en' => 'Long White Rice'],
            'parent_id' => $longRice->id,
        ]);

        /* =======================
         * Vendor
         * ======================= */

        $vendor = Vendor::create([
            'name' => ['ar' => 'تاجر الكبسة', 'en' => 'Kabsa Vendor'],
            'owner_name' => 'أحمد',
            'owner_phone' => '0999999999',
            'contract_date' => $now,
            'contract_number' => 'KABSA001',
            'contract_duration_months' => 12,
            'commission_rate' => 5,
        ]);

        /* =======================
         * Shop
         * ======================= */

        $shop = Shop::create([
            'name' => ['ar' => 'فرع المزة', 'en' => 'Al-Mazza Branch'],
            'mobile' => '0998888888',
            'email' => 'mazza@kabsa.com',
            'area_id' => 1,
            'address' => ['ar' => 'المزة', 'en' => 'Al-Mazza'],
            'vendor_id' => $vendor->id,
            'working_hours' => ['sat-sun' => '08:00-20:00'],
        ]);

        /* =======================
         * Products
         * ======================= */

        // Short Rice
        $shortRiceProduct = Product::create([
            'category_id' => $shortWhiteRice->id,
            'vendor_id' => $vendor->id,
            'name' => ['ar' => 'رز قصير أبيض', 'en' => 'Short White Rice'],
            'description' => ['ar' => 'رز قصير أبيض', 'en' => 'Short White Rice'],
            'price' => 2000,
        ]);

        $this->attachRandomMedia($shortRiceProduct, $files);

        $shortVariant = ProductVariant::create([
            'product_id' => $shortRiceProduct->id,
            'attributes_values_ids' => [6],
        ]);

        $shortShopVariant = ShopProductVariant::create([
            'product_variant_id' => $shortVariant->id,
            'shop_id' => $shop->id,
            'quantity' => 100,
            'price' => 2000,
        ]);

        // Long Rice
        $longRiceProduct = Product::create([
            'category_id' => $longWhiteRice->id,
            'vendor_id' => $vendor->id,
            'name' => ['ar' => 'رز طويل أبيض', 'en' => 'Long White Rice'],
            'description' => ['ar' => 'رز طويل أبيض', 'en' => 'Long White Rice'],
            'price' => 2100,
        ]);

        $this->attachRandomMedia($longRiceProduct, $files);

        $longVariant = ProductVariant::create([
            'product_id' => $longRiceProduct->id,
            'attributes_values_ids' => [6],
        ]);

        ShopProductVariant::create([
            'product_variant_id' => $longVariant->id,
            'shop_id' => $shop->id,
            'quantity' => 80,
            'price' => 2100,
        ]);

        // Ghee
        $gheeCategory = Category::create([
            'name' => ['ar' => 'سمنة', 'en' => 'Ghee'],
            'parent_id' => $food->id,
        ]);

        $gheeProduct = Product::create([
            'category_id' => $gheeCategory->id,
            'vendor_id' => $vendor->id,
            'name' => ['ar' => 'سمنة', 'en' => 'Ghee'],
            'description' => ['ar' => 'سمنة', 'en' => 'Ghee'],
            'price' => 500,
        ]);

        $this->attachRandomMedia($gheeProduct, $files);

        $gheeVariant = ProductVariant::create([
            'product_id' => $gheeProduct->id,
            'attributes_values_ids' => [6],
        ]);

        $gheeShopVariant = ShopProductVariant::create([
            'product_variant_id' => $gheeVariant->id,
            'shop_id' => $shop->id,
            'quantity' => 50,
            'price' => 500,
        ]);

        /* =======================
         * Recipe
         * ======================= */

        $recipe = Recipe::create([
            'name' => ['ar' => 'كبسة رز', 'en' => 'Kabsa Rice'],
            'description' => ['ar' => 'كبسة رز بالدجاج', 'en' => 'Rice Kabsa with Chicken'],
            'image' => 'recipies/image3.jpg',
            'rating' => 4.5,
            'discount' => 20,
            'serves' => '2-4',
            'prepare_time' => '25',
            'video_url' => 'https://youtu.be/WJibKMiLXw8',
            'delivery_price' => 500,
        ]);

        /* =======================
         * Recipe Items
         * ======================= */

        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'shop_product_variant_id' => $shortShopVariant->id,
            'quantity' => 1,
            'switchable_category_id' => $riceBulgur->id,
            'is_required' => true,
            'min_quantity' => 1,
            'max_quantity' => 5,
        ]);

        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'shop_product_variant_id' => $gheeShopVariant->id,
            'quantity' => 1,
            'is_required' => true,
            'min_quantity' => 1,
            'max_quantity' => 3,
        ]);

        /* =======================
         * Steps
         * ======================= */

        $recipe->steps()->createMany([
            [
                'step_number' => 1,
                'instruction' => [
                    'ar' => 'اغلي الماء واطبخ الأرز.',
                    'en' => 'Boil water and cook the rice.'
                ],
                'time_minutes' => [
                    'ar' => '10 دقائق',
                    'en' => '10 minutes'
                ],
                'heat_level' => [
                    'ar' => 'نار عالية',
                    'en' => 'High heat'
                ],
            ],
            [
                'step_number' => 2,
                'instruction' => [
                    'ar' => 'أضف السمنة وامزج جيداً.',
                    'en' => 'Add ghee and mix well.'
                ],
                'time_minutes' => [
                    'ar' => '5 دقائق',
                    'en' => '5 minutes'
                ],
                'heat_level' => [
                    'ar' => 'نار متوسطة',
                    'en' => 'Medium heat'
                ],
            ],
        ]);

        /* =======================
         * Badges
         * ======================= */

        $recipe->badges()->attach([
            1 => ['position' => 'top'],
            2 => ['position' => 'bottom'],
            3 => ['position' => 'bottom'],
        ]);
    }

    private function attachRandomMedia($product, $files)
    {
        collect($files)
            ->shuffle()
            ->take(rand(2, 4))
            ->values()
            ->each(function ($file, $index) use ($product) {

                ProductMedia::create([
                    'mediable_id' => $product->id,
                    'mediable_type' => Product::class,
                    'collection' => 'product',
                    'path' => $file,
                    'order' => $index + 1,
                ]);
            });
    }
}
