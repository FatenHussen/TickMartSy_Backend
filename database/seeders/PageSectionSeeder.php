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

        $shopDisplayType = DisplayType::create([
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
            ['title' => 'Recipes', 'slug' => 'recipes'],
            ['title' => 'recipe details', 'slug' => 'recipe_details'],
            ['title' => 'brands', 'slug' => 'brands'],
            ['title' => 'brands details', 'slug' => 'brand_details'],
            ['title' => 'baskets', 'slug' => 'baskets'],
            ['title' => 'basket details', 'slug' => 'basket_details'],
            ['title' => 'products', 'slug' => 'products'],
            ['title' => 'product details', 'slug' => 'product_details'],
            ['title' => 'shops', 'slug' => 'shops'],
            ['title' => 'shop_details', 'slug' => 'shop_details'],
            ['title' => 'brands', 'slug' => 'brands'],
            ['title' => 'brand_details', 'slug' => 'brand_details'],
        ];



        /*
        |--------------------------------------------------------------------------
        | Manual Banners Section
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
        | Pages Banner Placement
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
        // $manualProductsSection = Section::create([
        //     'name' => ['en' => 'Manual Products', 'ar' => 'منتجات مختارة'],
        //     'type' => 'manual',
        // ]);

        // PageSection::create([
        //     'page_id' => $homePage->id,
        //     'section_id' => $manualProductsSection->id,
        //     'display_type_id' => $productDisplayType->id,
        //     'position' => 'after',
        //     'order' => 2,
        //     'filters' => [],
        // ]);

        // $products = Product::take(2)->get();

        // foreach ($products as $index => $product) {
        //     SectionItem::create([
        //         'section_id' => $manualProductsSection->id,
        //         'item_type' => Product::class,
        //         'item_id' => $product->id,
        //         'link' => '/product/' . $product->id,
        //         'order' => $index + 1
        //     ]);
        // }

        /*
        |--------------------------------------------------------------------------
        | Brands Section
        |--------------------------------------------------------------------------
        */
        $brandsSection = Section::create([
            'name' => ['en' => 'Brands', 'ar' => 'قسم البراندات'],
            'type' => 'api',
            'api_method' => 'brands',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'brands',
            'details_slug' => 'brand_details',
        ]);

        PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $brandsSection->id,
            'display_type_id' => $brandsDisplayType->id,
            'position' => 'before',
            'order' => 4,
            'filters' => []
        ]);

        /*
        |--------------------------------------------------------------------------
        | Recipes Section
        |--------------------------------------------------------------------------
        */
        $recipeSection = Section::create([
            'name' => ['en' => 'recipe', 'ar' => 'قسم الطبخة'],
            'type' => 'api',
            'api_method' => 'recipes',
            'filters' => ['discount' => ['type' => 'number']],
            'see_more' => true,
            'see_more_slug' => 'recipes',
            'details_slug' => 'recipe_details',

        ]);

        PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $recipeSection->id,
            'display_type_id' => $recipeDisplayType->id,
            'position' => 'after',
            'order' => 5,
            'filters' => []
        ]);


        PageSection::create([
            'name' => ['en' => 'recipe 50% discount', 'ar' => 'طبخات بخصومات تصل ل 50%'],
            'page_id' => $homePage->id,
            'section_id' => $recipeSection->id,
            'display_type_id' => $recipeDisplayType->id,
            'position' => 'after',
            'order' => 5,
            'filters' => [
                'discount' => ['value' => 50, 'operator' => '<=']
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | Baskets Section
        |--------------------------------------------------------------------------
        */
        $basketSection = Section::create([
            'name' => ['en' => 'basket', 'ar' => 'قسم السلات'],
            'type' => 'api',
            'api_method' => 'baskets',
            'see_more' => true,
            'see_more_slug' => 'baskets',
            'details_slug' => 'basket_details',
            'filters' => []
        ]);

        PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $basketSection->id,
            'display_type_id' => $basketsDisplayType->id,
            'position' => 'after',
            'order' => 6,
            'filters' => []
        ]);

        PageSection::create([
            'name' => ['en' => 'schedule basket', 'ar' => 'قسم السلات المجدولة'],

            'page_id' => $homePage->id,
            'section_id' => $basketSection->id,
            'display_type_id' => $basketsDisplayType->id,
            'position' => 'after',
            'order' => 6,
            'filters' => []
        ]);

        $recipeSection = Section::create([
            'name' => ['en' => 'recipe', 'ar' => 'قسم الطبخة'],
            'type' => 'api',
            'api_method' => 'products',
            'filters' => [
                'discount' => ['type' => 'number']
            ],
            'see_more' => true,
            'see_more_slug' => 'recipes',
            'details_slug' => 'recipe_details',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Trending Products Section
        |--------------------------------------------------------------------------
        */

        $productsSection = Section::create([
            'name' => ['en' => 'Products', 'ar' => 'المنتجات'],
            'type' => 'api',
            'api_method' => 'products',
            'filters' => [
                'category_id' => ['type' => 'select', 'url' => 'admin/categories'],
                'price_max'   => ['type' => 'number'],
                'price_max' =>  ['type' => 'number'],
                'shop_id' => ['type' => 'select', 'url' => 'admin/shops'],
                'brand_id' => ['type' => 'select', 'url' => 'admin/brands'],
                'type' => [
                    'type' => 'select',
                    'items' => [
                        'new',
                        'trend',
                        'top_rated',
                        'offers',
                        'recommended',
                        'for_you',
                        'search_based',
                    ]
                ],
            ],
            'see_more' => true,
            'see_more_slug' => 'products',
            'details_slug'  => 'product_details',
        ]);

        PageSection::create([
            'name' => ['en' => 'Trend Products', 'ar' => 'المنتجات التريند'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'order' => 5,
            'filters' => [
                'type' => 'trend',
            ]
        ]);
        PageSection::create([
            'name' => ['en' => 'New arrival products', 'ar' => 'منتجات وصلت حديثا'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'order' => 6,
            'filters' => [
                'type' => 'new',
            ]
        ]);
        PageSection::create([
            'name' => ['en' => 'Top rated products', 'ar' => 'منتجات اعلى تقييما'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'order' => 7,
            'filters' => [
                'type' => 'top_rated',
            ]
        ]);

        PageSection::create([
            'name' => ['en' => 'Offers', 'ar' => 'العروض'],
            'page_id' => $homePage->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'order' => 8,
            'filters' => [
                'type' => 'offers',
            ]
        ]);

        $shopSection = Section::create([
            'name' => ['en' => 'Shops', 'ar' => 'المتاجر'],
            'type' => 'api',
            'api_method' => 'shops',
            'filters' => [
                'type' => [
                    'type' => 'select',
                    'items' => [
                        'nearby',
                        'offers',
                        'active',
                        'top_rated',
                    ]
                ],
            ],
            'see_more' => true,
            'see_more_slug' => 'shops',
            'details_slug'  => 'shop_details',
        ]);

        PageSection::create([
            'name' => ['en' => 'Nearby Shops', 'ar' => 'المتاجر القريبة'],
            'page_id' => $homePage->id,
            'section_id' => $shopSection->id,
            'display_type_id' => $shopDisplayType->id,
            'position' => 'after',
            'order' => 9,
            'filters' => [
                'type' => 'nearby',
            ]
        ]);
    }
}
