<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\Section;
use App\Models\PageSection;
use App\Models\DisplayType;
use App\Models\Banner;
use App\Models\SectionItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Recipe;

class PageSectionSeeder extends Seeder
{
    public function run()
    {
        $banner1 = Banner::create([
            'title' => ['en' => 'Dis 50%', 'ar' => 'خصم حتى 50%'],
            'image' => '/images/banners/banner1.jpg',
            'link' => '/sale',
        ]);

        $banner2 = Banner::create([
            'title' => ['en' => 'New Arrivals', 'ar' => 'وصل حديثا'],
            'image' => '/images/banners/banner2.jpg',
            'link' => '/new-arrivals',
        ]);

        $homePage = Page::create(['title' => 'Home', 'slug' => 'home']);
        $shopPage = Page::create(['title' => 'Shop', 'slug' => 'shop']);

        $slider = DisplayType::create([
            'name' => 'Slider',
            'preview_image' => '/images/display/slider.png',
            'fields' => ['image', 'title', 'price']
        ]);

        $grid = DisplayType::create([
            'name' => 'Grid',
            'preview_image' => '/images/display/grid.png',
            'fields' => ['image', 'title', 'price', 'brand']
        ]);

        // API Section with schema filters
        $trendingProductsSection = Section::create([
            'name' => ['en' => 'Trending Products', 'ar' => 'المنتجات الترند'],
            'type' => 'api',
            'api_method' => 'trending_products',
            'filters' => [
                'category_id' => ['type' => 'select'],
                'price_max' => ['type' => 'number']
            ]
        ]);

        $manualProductsSection = Section::create([
            'name' => ['en' => 'Manual Products', 'ar' => 'منتجات مختارة'],
            'type' => 'manual'
        ]);

        $manualBannerSection = Section::create([
            'name' => ['en' => 'Manual Banners', 'ar' => 'إعلانات'],
            'type' => 'manual',
            'manual_model' => 'Banner'
        ]);

        $manualRecipesSection = Section::create([
            'name' => ['en' => 'Manual Recipes', 'ar' => 'وصفات'],
            'type' => 'manual',
            'manual_model' => 'Recipe'
        ]);

        // Trending Products - Home
        $homeTrending = PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $trendingProductsSection->id,
            'display_type_id' => $slider->id,
            'position' => 'before',
            'order' => 1,
            'filters' => ['category_id' => 1, 'price_max' => 100]
        ]);

        // Manual Products - Home
        $homeManualProducts = PageSection::create([
            'name' => ['en' => 'Manual Products', 'ar' => 'منتجات مختارة'],
            'page_id' => $homePage->id,
            'section_id' => $manualProductsSection->id,
            'display_type_id' => $grid->id,
            'position' => 'after',
            'order' => 2
        ]);

        // Manual Banners - Home
        $homeManualBanners = PageSection::create([
            'name' => ['en' => 'Manual Banners', 'ar' => 'إعلانات'],
            'page_id' => $homePage->id,
            'section_id' => $manualBannerSection->id,
            'display_type_id' => $slider->id,
            'position' => 'after',
            'order' => 3
        ]);

        // Manual Recipes - Shop (optional)
        $shopManualRecipes = PageSection::create([
            'name' => ['en' => 'Manual Recipes', 'ar' => 'وصفات'],
            'page_id' => $shopPage->id,
            'section_id' => $manualRecipesSection->id,
            'display_type_id' => $grid->id,
            'position' => 'after',
            'order' => 1
        ]);

        // Manual Products Items
        $product1 = Product::first(); // مثال
        $product2 = Product::skip(1)->first();

        SectionItem::create([
            'section_id' => $manualProductsSection->id,
            'item_type' => 'App\Models\Product',
            'item_id' => $product1->id,
            'link' => '/product/' . $product1->id,
            'order' => 1
        ]);

        SectionItem::create([
            'section_id' => $manualProductsSection->id,
            'item_type' => 'App\Models\Product',
            'item_id' => $product2->id,
            'link' => '/product/' . $product2->id,
            'order' => 2
        ]);

        // Manual Banners Items
        SectionItem::create([
            'section_id' => $manualBannerSection->id,
            'item_type' => 'App\Models\Banner',
            'item_id' => $banner1->id,
            'link' => $banner1->link,
            'order' => 1
        ]);

        SectionItem::create([
            'section_id' => $manualBannerSection->id,
            'item_type' => 'App\Models\Banner',
            'item_id' => $banner2->id,
            'link' => $banner2->link,
            'order' => 2
        ]);
    }
}
