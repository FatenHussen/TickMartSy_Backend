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
            'name' => ['ar' => 'بقوليات', 'en' => 'Legumes'],
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
            'price' => 4,
            'approval_status' => \App\Enums\ProductApprovalStatus::APPROVED->value
        ]);

        $this->attachRandomMedia($shortRiceProduct, $files);

        $shortVariant = ProductVariant::create([
            'product_id' => $shortRiceProduct->id,
            'attributes_values_ids' => [6],
            'price' => 4,
            'quantity' => 100,
        ]);

        $shortShopVariant = ShopProductVariant::create([
            'product_variant_id' => $shortVariant->id,
            'shop_id' => $shop->id,
        ]);

        // Long Rice
        $longRiceProduct = Product::create([
            'category_id' => $longWhiteRice->id,
            'vendor_id' => $vendor->id,
            'name' => ['ar' => 'رز طويل أبيض', 'en' => 'Long White Rice'],
            'description' => ['ar' => 'رز طويل أبيض', 'en' => 'Long White Rice'],
            'price' => 5,
            'approval_status' => \App\Enums\ProductApprovalStatus::APPROVED->value

        ]);

        $this->attachRandomMedia($longRiceProduct, $files);

        $longVariant = ProductVariant::create([
            'product_id' => $longRiceProduct->id,
            'attributes_values_ids' => [6],
            'price' => 6,
            'quantity' => 80,
        ]);

        ShopProductVariant::create([
            'product_variant_id' => $longVariant->id,
            'shop_id' => $shop->id,
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
            'price' => 5,
            'approval_status' => \App\Enums\ProductApprovalStatus::APPROVED->value

        ]);

        $this->attachRandomMedia($gheeProduct, $files);

        $gheeVariant = ProductVariant::create([
            'product_id' => $gheeProduct->id,
            'attributes_values_ids' => [6],
            'price' => 5,
            'quantity' => 50,
        ]);

        $gheeShopVariant = ShopProductVariant::create([
            'product_variant_id' => $gheeVariant->id,
            'shop_id' => $shop->id,
        ]);

        /* =======================
         * Recipes
         * ======================= */

        $recipes = [
            [
                'name' => ['ar' => 'أرز الكبسة', 'en' => 'Kabsa Rice'],
                'description' => ['ar' => 'أرز كبسة بالدجاج مع التوابل الشرقية', 'en' => 'Rice Kabsa with Chicken and Eastern Spices'],
                'image' => 'recipies/image3.jpg',
                'rating' => 4.5,
                'discount' => 20,
                'serves' => '2-4',
                'prepare_time' => '25',
                'video_url' => 'https://youtu.be/WJibKMiLXw8',
                'video_title' => ['ar' => 'فيديو كبسة الدجاج', 'en' => 'Chicken Kabsa Video'],
                'video_desc' => ['ar' => 'شرح مختصر لطريقة تحضير الكبسة بالشكل التقليدي', 'en' => 'Step-by-step guide on traditional Kabsa preparation'],
                'delivery_price' => 7,
            ],
            [
                'name' => ['ar' => 'أرز المندي', 'en' => 'Mandi Rice'],
                'description' => ['ar' => 'مندي تقليدي بالتوابل العربية الأصيلة', 'en' => 'Traditional Mandi with Authentic Arabic Spices'],
                'image' => 'recipies/image3.jpg',
                'rating' => 4.1,
                'discount' => 10,
                'serves' => '3-5',
                'prepare_time' => '35',
                'video_url' => 'https://youtu.be/WJibKMiLXw8',
                'video_title' => ['ar' => 'فيديو أرز المندي', 'en' => 'Mandi Rice Video'],
                'video_desc' => ['ar' => 'طريقة التحضير مع توابل الأصالة', 'en' => 'Preparation with authentic Arabic spices'],
                'delivery_price' => 7,
            ],
            [
                'name' => ['ar' => 'أرز البرياني', 'en' => 'Biryani Rice'],
                'description' => ['ar' => 'برياني عطري بالتوابل الهندية الفاخرة', 'en' => 'Aromatic Biryani with Premium Indian Spices'],
                'image' => 'recipies/image2.jpg',
                'rating' => 4.7,
                'discount' => 15,
                'serves' => '2-3',
                'prepare_time' => '30',
                'video_url' => 'https://youtu.be/WJibKMiLXw8',
                'video_title' => ['ar' => 'فيديو أرز البرياني', 'en' => 'Biryani Rice Video'],
                'video_desc' => ['ar' => 'مكونات البرياني الهندي ومقاديرها', 'en' => 'Ingredients and measurements for aromatic Biryani'],
                'delivery_price' => 8,
            ],
            [
                'name' => ['ar' => 'برغل بالتوابل', 'en' => 'Spiced Bulgur'],
                'description' => ['ar' => 'برغل خفيف مع خلطة التوابل الشامية', 'en' => 'Light Bulgur with Levantine Spice Mix'],
                'image' => 'recipies/image2.jpg',
                'rating' => 3.9,
                'discount' => 5,
                'serves' => '2-4',
                'prepare_time' => '20',
                'video_url' => 'https://youtu.be/WJibKMiLXw8',
                'video_title' => ['ar' => 'فيديو برغل بالتوابل', 'en' => 'Spiced Bulgur Video'],
                'video_desc' => ['ar' => 'تحضير البرغل الخفيف مع خلطة التوابل', 'en' => 'Light bulgur with Levantine spice mix tutorial'],
                'delivery_price' => 9,
            ],
            [
                'name' => ['ar' => 'أرز بالسمنة', 'en' => 'Ghee Rice'],
                'description' => ['ar' => 'أرز غني بالسمنة البلدية الطبيعية', 'en' => 'Rich Rice with Natural Homemade Ghee'],
                'image' => 'recipies/image2.jpg',
                'rating' => 4.3,
                'discount' => 12,
                'serves' => '2-4',
                'prepare_time' => '22',
                'video_url' => 'https://youtu.be/WJibKMiLXw8',
                'video_title' => ['ar' => 'فيديو أرز بالسمنة', 'en' => 'Ghee Rice Video'],
                'video_desc' => ['ar' => 'المكونات والوقت المناسب لخلطة السمنة', 'en' => 'Ingredients and timing for the ghee-laden rice'],
                'delivery_price' => 9,
            ],
        ];

        $steps = [
            [
                'step_number' => 1,
                'instruction' => [
                    'ar' => 'اغلي الماء واطبخ الأرز حتى ينضج',
                    'en' => 'Boil water and cook the rice until done'
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
                    'ar' => 'أضف السمنة وقلب جيداً حتى تتداخل النكهات',
                    'en' => 'Add ghee and mix well until flavors blend'
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
        ];

        foreach ($recipes as $recipeData) {
            $recipe = Recipe::create($recipeData);

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
                'quantity' => 2,
                'is_required' => false,
                'min_quantity' => 1,
                'max_quantity' => 3,
            ]);

            /* =======================
             * Steps
             * ======================= */

            $recipe->steps()->createMany($steps);

            /* =======================
             * Badges
             * ======================= */

            $recipe->badges()->sync([1, 2, 3]);
        }
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
