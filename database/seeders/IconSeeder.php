<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Icon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class IconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create icons
        $icons = [
            [
                'name' => ['ar' => 'جديد', 'en' => 'New'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'منتج جديد', 'en' => 'New product'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'عرض خاص', 'en' => 'Special Offer'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'عرض لفترة محدودة', 'en' => 'Limited time offer'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'الأكثر مبيعاً', 'en' => 'Best Seller'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'من أكثر المنتجات مبيعاً', 'en' => 'One of the best selling products'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'توصيل مجاني', 'en' => 'Free Delivery'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'توصيل مجاني للمنتج', 'en' => 'Free delivery for this product'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'خصم', 'en' => 'Discount'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'منتج عليه خصم', 'en' => 'Product on discount'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'محدود', 'en' => 'Limited'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'كمية محدودة', 'en' => 'Limited quantity'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'عضوي', 'en' => 'Organic'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'منتج عضوي طبيعي', 'en' => 'Natural organic product'],
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'مستورد', 'en' => 'Imported'],
                'image' => 'icons/icon.png',
                'description' => ['ar' => 'منتج مستورد', 'en' => 'Imported product'],
                'is_active' => true,
            ],
        ];

        foreach ($icons as $iconData) {
            Icon::updateOrCreate(
                ['image' => $iconData['image']],
                $iconData
            );
        }

        $this->command->info('Icons created successfully!');

        // Attach icons to products
        $this->attachIconsToProducts();
    }

    /**
     * Attach icons to random products
     */
    private function attachIconsToProducts(): void
    {
        $icons = Icon::all();
        $products = Product::limit(20)->get();

        if ($icons->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No icons or products found to attach.');
            return;
        }

        foreach ($products as $product) {
            // Randomly select 1-3 icons for each product
            $randomIcons = $icons->random(rand(1, min(3, $icons->count())));

            // Attach icons to product
            $product->icons()->sync($randomIcons->pluck('id')->toArray());
        }

        $this->command->info('Icons attached to products successfully!');
    }
}
