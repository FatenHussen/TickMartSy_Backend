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
        // ---------- Banners ----------
        $banner1 = Banner::create([
            'title' => 'خصم 50%',
            'image' => '/images/banners/banner1.jpg',
            'link' => '/sale',
            'order' => 1,
            'active' => true,
        ]);

        $banner2 = Banner::create([
            'title' => 'وصل حديثًا',
            'image' => '/images/banners/banner2.jpg',
            'link' => '/new-arrivals',
            'order' => 2,
            'active' => true,
        ]);

        // ---------- Pages ----------
        $homePage = Page::create(['title' => 'Home', 'slug' => 'home']);
        $shopPage = Page::create(['title' => 'Shop', 'slug' => 'shop']);

        // ---------- Display Types ----------
        $slider = DisplayType::create([
            'name' => 'Slider',
            'preview_image' => '/images/display/slider.png',
            'fields' => json_encode(['image', 'title', 'price'])
        ]);
        $grid = DisplayType::create([
            'name' => 'Grid',
            'preview_image' => '/images/display/grid.png',
            'fields' => json_encode(['image', 'title', 'price', 'brand'])
        ]);

        // ---------- Sections ----------
        $trendingProductsSection = Section::create([
            'name' => json_encode(['en' => 'Trending Products', 'ar' => 'المنتجات الترند']),
            'type' => 'api',
            'api_source' => 'trending_products',
            'filters' => json_encode([
                'category_id' => ['type' => 'select'],
                'price_max' => ['type' => 'number']
            ])
        ]);

        $manualProductsSection = Section::create([
            'name' => json_encode(['en' => 'Manual Products', 'ar' => 'منتجات مختارة']),
            'type' => 'manual'
        ]);

        $manualBannerSection = Section::create([
            'name' => json_encode(['en' => 'Manual Banners', 'ar' => 'إعلانات']),
            'type' => 'manual'
        ]);

        $manualRecipesSection = Section::create([
            'name' => json_encode(['en' => 'Manual Recipes', 'ar' => 'وصفات']),
            'type' => 'manual'
        ]);

        // ---------- Page Sections ----------
        $homeTrending = PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $trendingProductsSection->id,
            'display_type_id' => $slider->id,
            'position' => 'before',
            'order' => 1,
            'filters' => json_encode(['category_id' => 1, 'price_max' => 100])
        ]);

        $homeManualProducts = PageSection::create([
            'name' => json_encode(['en' => 'Manual Products', 'ar' => 'منتجات مختارة']),

            'page_id' => $homePage->id,
            'section_id' => $manualProductsSection->id,
            'display_type_id' => $grid->id,
            'position' => 'after',
            'order' => 2
        ]);

        $homeManualBanners = PageSection::create([
            'name' => json_encode(['en' => 'Manual Banners', 'ar' => 'إعلانات']),
            'page_id' => $homePage->id,
            'section_id' => $manualBannerSection->id,
            'display_type_id' => $slider->id,
            'position' => 'after',
            'order' => 3
        ]);

        // $shopManualRecipes = PageSection::create([
        //     'page_id' => $shopPage->id,
        //     'section_id' => $manualRecipesSection->id,
        //     'display_type_id' => $grid->id,
        //     'position' => 'after',
        //     'order' => 1
        // ]);



        // ---------- Section Items ----------
        // Manual Products
        $product1 = Category::first();
        $product2 = Category::skip(1)->first();

        SectionItem::create([
            'section_id' => $manualProductsSection->id,
            'item_type' => 'Product',
            'item_id' => $product1->id,
            'link' => '/product/' . $product1->id,
            'order' => 1
        ]);
        SectionItem::create([
            'section_id' => $manualProductsSection->id,
            'item_type' => 'Product',
            'item_id' => $product2->id,
            'link' => '/product/' . $product2->id,
            'order' => 2
        ]);

        // Manual Banners
        SectionItem::create([
            'section_id' => $manualBannerSection->id,
            'item_type' => 'Banner',
            'item_id' => $banner1->id,
            'link' => $banner1->link,
            'order' => 1
        ]);
        SectionItem::create([
            'section_id' => $manualBannerSection->id,
            'item_type' => 'Banner',
            'item_id' => $banner2->id,
            'link' => $banner2->link,
            'order' => 2
        ]);
    }
}
