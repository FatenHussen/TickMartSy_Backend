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
        $suggestedBasketsDisplayType = DisplayType::create([
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
        // $shopPage = Page::create(['title' => 'Shop', 'slug' => 'shop']);


        // API Section with schema filters
        $trendingProductsSection = Section::create([
            'name' => ['en' => 'Trending Products', 'ar' => 'المنتجات الترند'],
            'type' => 'api',
            'api_method' => 'trending_products',
            'filters' => [
                'category_id' => ['type' => 'select', 'url' => 'admin/categories'],
                'price_max' => ['type' => 'number']
            ],
            'see_more' => true,
            'see_more_slug' => 'products',
            'details_slug' => 'product_details',
        ]);
        // Trending Products - Home
        $homeTrending = PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $trendingProductsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'before',
            'order' => 1,
            'filters' => ['category_id' => 1, 'price_max' => 100]
        ]);


        $manualBannerSection = Section::create([
            'name' => ['en' => 'Manual Banners', 'ar' => 'إعلانات'],
            'type' => 'manual',
            'manual_model' => 'banner'
        ]);
        // Manual Banners - Home
        $homeManualBanners = PageSection::create([
            'name' => ['en' => 'Manual Banners', 'ar' => 'إعلانات'],
            'page_id' => $homePage->id,
            'section_id' => $manualBannerSection->id,
            'display_type_id' => $bannerDisplayType->id,
            'position' => 'after',
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


        // Manual Products Items
        $product1 = Product::first(); // مثال
        $product2 = Product::skip(1)->first();

        $manualProductsSection = Section::create([
            'name' => ['en' => 'Manual Products', 'ar' => 'منتجات مختارة'],
            'type' => 'manual'
        ]);

        // Manual Banners - Home
        $homeManualBanners = PageSection::create([
            'name' => ['en' => 'Manual Products', 'ar' => 'منتجات مختارة'],
            'page_id' => $homePage->id,
            'section_id' => $manualProductsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'order' => 2
        ]);

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

        $brandSection = Section::create([
            'name' => ['en' => 'Brands', 'ar' => 'قسم البراندات'],
            'type' => 'api',
            'api_method' => 'brands',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'brands',
            'details_slug' => 'brand_details',
        ]);

        $homeTrending = PageSection::create([
            'page_id' => $homePage->id,
            'section_id' => $brandSection->id,
            'display_type_id' => $brandsDisplayType->id,
            'position' => 'before',
            'order' => 4,
            'filters' => []
        ]);
    }
}
