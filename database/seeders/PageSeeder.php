<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates empty app pages (no banners, products, or demo sections).
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title' => 'Home', 'slug' => 'home'],
            ['title' => 'Welcome', 'slug' => 'welcome'],
            ['title' => 'Intro', 'slug' => 'intro'],
            ['title' => 'Recipes', 'slug' => 'recipes'],
            ['title' => 'Recipe details', 'slug' => 'recipe_details'],
            ['title' => 'Brands', 'slug' => 'brands'],
            ['title' => 'Brand details', 'slug' => 'brand_details'],
            ['title' => 'Baskets', 'slug' => 'baskets'],
            ['title' => 'Basket details', 'slug' => 'basket_details'],
            ['title' => 'Products', 'slug' => 'products'],
            ['title' => 'Categories', 'slug' => 'categories'],
            ['title' => 'Product details', 'slug' => 'product_details'],
            ['title' => 'Shops', 'slug' => 'shops'],
            ['title' => 'Shop details', 'slug' => 'shop_details'],
            ['title' => 'Orders', 'slug' => 'orders'],
            ['title' => 'Order details', 'slug' => 'order_details'],
            ['title' => 'Cart', 'slug' => 'cart'],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                ['title' => $page['title']]
            );
        }
    }
}
