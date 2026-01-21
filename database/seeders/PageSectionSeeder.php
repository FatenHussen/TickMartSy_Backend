<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{
    Page,
    Section,
    PageSection,
    DisplayType,
    Banner,
    SectionItem,
    Product
};

class PageSectionSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Display Types
        |--------------------------------------------------------------------------
        */
        $bannerDisplayType = DisplayType::create([
            'manual_model' => 'brand',
            'image' => '/images/display/slider.png',
            'fields' => ['image', 'title', 'price']
        ]);

        $productDisplayType = DisplayType::create([
            'manual_model' => 'product',
            'image' => '/images/display/grid.png',
            'fields' => ['image', 'title', 'price', 'brand']
        ]);

        $storeDisplayType = DisplayType::create([
            'manual_model' => 'shop',
            'image' => '/images/display/grid.png',
            'fields' => ['image', 'title', 'price', 'brand']
        ]);

        $basketsDisplayType = DisplayType::create([
            'manual_model' => 'basket',
            'image' => '/images/display/grid.png',
            'fields' => ['image', 'title', 'price', 'brand']
        ]);

        $suggestedBasketsDisplayType = DisplayType::create([
            'manual_model' => 'suggested-basket',
            'image' => '/images/display/grid.png',
            'fields' => ['image', 'title', 'price', 'brand']
        ]);

        $brandsDisplayType = DisplayType::create([
            'manual_model' => 'brand',
            'image' => '/images/display/grid.png',
            'fields' => ['image', 'title', 'price', 'brand']
        ]);

        $recipeDisplayType = DisplayType::create([
            'manual_model' => 'recipe',
            'image' => '/images/display/recipe.png',
            'fields' => ['image', 'title', 'decription', 'price', 'brand']
        ]);

        /*
        |--------------------------------------------------------------------------
        | Banners
        |--------------------------------------------------------------------------
        */
        $banner1 = Banner::create([
            'title' => ['en' => 'Dis 50%', 'ar' => 'خصم حتى 50%'],
            'image' => '/images/banners/banner1.jpg',
            'link'  => '/sale',
        ]);

        $banner2 = Banner::create([
            'title' => ['en' => 'New Arrivals', 'ar' => 'وصل حديثا'],
            'image' => '/images/banners/banner2.jpg',
            'link'  => '/new-arrivals',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pages
        |--------------------------------------------------------------------------
        */
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            ['title' => 'Home']
        );

        $pages = [
            ['title' => 'Store Details', 'slug' => 'store-details'],
            ['title' => 'Category', 'slug' => 'category'],
            ['title' => 'Brand Products', 'slug' => 'brand-products'],
            ['title' => 'Cooking Recipes', 'slug' => 'cooking-recipes'],
            ['title' => 'Subscription Packages', 'slug' => 'subscription-packages'],
            ['title' => 'Search Results', 'slug' => 'search-results'],
            ['title' => 'All Baskets', 'slug' => 'all-baskets'],
            ['title' => 'Cart', 'slug' => 'cart'],
            ['title' => 'Checkout', 'slug' => 'checkout'],
            ['title' => 'Review Order', 'slug' => 'review-order'],
        ];

        /*
        |--------------------------------------------------------------------------
        | API Sections
        |--------------------------------------------------------------------------
        */

        // Trending Products
        $trendingProductsSection = Section::create([
            'name' => ['en' => 'Trending Products', 'ar' => 'المنتجات الترند'],
            'type' => 'api',
            'api_method' => 'trending_products',
            'filters' => [
                'category_id' => ['type' => 'select', 'url' => 'admin/categories'],
                'price_max'   => ['type' => 'number'],
            ],
            'see_more' => true,
            'see_more_slug' => 'products',
            'details_slug'  => 'product_details',
        ]);

        PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $trendingProductsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'before',
            'order' => 1,
            'filters' => ['category_id' => 1, 'price_max' => 100]
        ]);

        /*
        |--------------------------------------------------------------------------
        | Manual Banner Section
        |--------------------------------------------------------------------------
        */
        $bannerSection = Section::firstOrCreate(
            ['type' => 'manual', 'manual_model' => 'banner'],
            ['name' => ['en' => 'Banners', 'ar' => 'إعلانات']]
        );

        foreach ([$banner1, $banner2] as $index => $banner) {
            SectionItem::firstOrCreate([
                'section_id' => $bannerSection->id,
                'item_type'  => Banner::class,
                'item_id'    => $banner->id,
            ], [
                'order' => $index + 1
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Banner Positions on Pages
        |--------------------------------------------------------------------------
        */
        $bannerPositions = [
            'store-details'   => 'before',
            'category'        => 'before',
            'brand-products'  => 'after',
            'cooking-recipes' => 'before',
            'subscription-packages' => 'after',
            'search-results'  => 'middle',
            'all-baskets'     => 'middle',
            'cart'            => 'before',
            'checkout'        => 'before',
            'review-order'    => 'after',
        ];

        foreach ($pages as $pageData) {
            $page = Page::firstOrCreate(
                ['slug' => $pageData['slug']],
                ['title' => $pageData['title']]
            );

            PageSection::firstOrCreate([
                'page_id' => $page->id,
                'section_id' => $bannerSection->id,
            ], [
                'display_type_id' => $bannerDisplayType->id,
                'position' => $bannerPositions[$page->slug] ?? 'before',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Manual Products Section
        |--------------------------------------------------------------------------
        */
        $manualProductsSection = Section::create([
            'name' => ['en' => 'Manual Products', 'ar' => 'منتجات مختارة'],
            'type' => 'manual',
        ]);

        PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $manualProductsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'order' => 2
        ]);

        $products = Product::take(2)->get();

        foreach ($products as $index => $product) {
            SectionItem::create([
                'section_id' => $manualProductsSection->id,
                'item_type' => Product::class,
                'item_id' => $product->id,
                'link' => '/product/' . $product->id,
                'order' => $index + 1
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Brands / Recipes / Baskets Sections
        |--------------------------------------------------------------------------
        */

        $this->createApiSection(
            $homePage,
            'Brands',
            'قسم البراندات',
            'brands',
            $brandsDisplayType,
            4,
            'brands',
            'brand_details'
        );

        $this->createApiSection(
            $homePage,
            'recipe',
            'قسم الطبخة',
            'recipes',
            $recipeDisplayType,
            5,
            'recipes',
            'recipe_details'
        );

        $this->createApiSection(
            $homePage,
            'basket',
            'قسم السلات',
            'baskets',
            $basketsDisplayType,
            6,
            'baskets',
            'basket_details'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Method
    |--------------------------------------------------------------------------
    */
    private function createApiSection(
        Page $page,
        string $enName,
        string $arName,
        string $method,
        DisplayType $displayType,
        int $order,
        string $seeMore,
        string $details
    ) {
        $section = Section::create([
            'name' => ['en' => $enName, 'ar' => $arName],
            'type' => 'api',
            'api_method' => $method,
            'see_more' => true,
            'see_more_slug' => $seeMore,
            'details_slug' => $details,
            'filters' => [],
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $section->id,
            'display_type_id' => $displayType->id,
            'position' => 'after',
            'order' => $order,
        ]);
    }
}
