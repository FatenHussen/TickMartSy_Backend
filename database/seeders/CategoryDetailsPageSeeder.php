<?php

namespace Database\Seeders;

use App\Enums\VariantSection;
use App\Models\DisplayType;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use Illuminate\Database\Seeder;

/**
 * Seeds the shared "category-details" template page. Every category reuses this
 * page (scoped at request time by category_id / parent_id), so no per-category
 * CMS data needs to be duplicated.
 *
 * Safe to run multiple times.
 */
class CategoryDetailsPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::firstOrCreate(
            ['slug' => 'category-details'],
            ['title' => 'Category Details']
        );

        // Already seeded: don't duplicate sections.
        if (PageSection::where('page_id', $page->id)->exists()) {
            return;
        }

        $categoryDisplayType = DisplayType::firstOrCreate(
            ['manual_model' => 'category', 'allowed_page_slugs' => null],
            ['image' => 'images/display/category.png']
        );

        $productDisplayType = DisplayType::firstOrCreate(
            ['manual_model' => 'product', 'allowed_page_slugs' => null],
            ['image' => 'images/display/product.png']
        );

        // 1) Subcategories block: children of the current category (parent_id).
        $childrenSection = Section::create([
            'name' => ['en' => 'Subcategories', 'ar' => 'الأقسام الفرعية'],
            'type' => 'api',
            'api_method' => 'categories',
            'filters' => [],
            'see_more' => false,
            'manual_model' => 'category',
        ]);

        PageSection::create([
            'name' => ['en' => 'Subcategories', 'ar' => 'الأقسام الفرعية'],
            'page_id' => $page->id,
            'section_id' => $childrenSection->id,
            'display_type_id' => $categoryDisplayType->id,
            'position' => 'after',
            'variant' => VariantSection::Square->value,
            'order' => 1,
            'filters' => [],
            'is_active' => true,
        ]);

        // 2) Products block: products of the current category subtree (category_id).
        $productsSection = Section::create([
            'name' => ['en' => 'Products', 'ar' => 'المنتجات'],
            'type' => 'api',
            'api_method' => 'products',
            'filters' => [],
            'see_more' => true,
            'see_more_slug' => 'products',
            'details_slug' => 'product_details',
            'manual_model' => 'product',
        ]);

        PageSection::create([
            'name' => ['en' => 'Products', 'ar' => 'المنتجات'],
            'page_id' => $page->id,
            'section_id' => $productsSection->id,
            'display_type_id' => $productDisplayType->id,
            'position' => 'after',
            'variant' => VariantSection::Vertical->value,
            'order' => 2,
            'filters' => [],
            'is_active' => true,
        ]);
    }
}
