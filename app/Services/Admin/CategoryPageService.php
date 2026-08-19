<?php

namespace App\Services\Admin;

use App\Enums\VariantSection;
use App\Models\Category;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Section;
use App\Support\DisplayTypeCatalog;
use Illuminate\Support\Facades\DB;

/**
 * Keeps every category paired with its own Page Builder page.
 *
 * When a category is created it gets a dedicated page (visible in the Pages list)
 * pre-seeded with two default blocks — its subcategories and its products —
 * scoped to that category. Admins can then add sliders, ad banners, or any other
 * section to that page. Deleting the category cascades to the page via FK.
 */
class CategoryPageService
{
    /**
     * Ensure the given category has a page. Creates it (with default sections)
     * the first time, and keeps the title in sync afterwards.
     */
    public function syncForCategory(Category $category): Page
    {
        $page = Page::query()->where('category_id', $category->id)->first();

        if ($page) {
            $this->syncTitle($category, $page);

            return $page;
        }

        return DB::transaction(function () use ($category) {
            $page = Page::create([
                'title'       => $this->resolveTitle($category),
                'slug'        => $this->uniqueSlug($category),
                'category_id' => $category->id,
                'filters'     => ['category_id' => $category->id],
            ]);

            $this->seedDefaultSections($page, $category);

            return $page;
        });
    }

    /** Update the page title when a category is renamed. */
    public function syncTitle(Category $category, ?Page $page = null): void
    {
        $page ??= Page::query()->where('category_id', $category->id)->first();

        if (!$page) {
            return;
        }

        $title = $this->resolveTitle($category);

        if ($page->title !== $title) {
            $page->update(['title' => $title]);
        }
    }

    /**
     * Default content so a fresh category page is never empty:
     * 1) its direct subcategories, 2) its products (whole subtree).
     */
    private function seedDefaultSections(Page $page, Category $category): void
    {
        $categoryDisplayTypeId = DisplayTypeCatalog::idFor('category');
        $productDisplayTypeId = DisplayTypeCatalog::idFor('product');

        $childrenSection = Section::create([
            'name'         => ['en' => 'Subcategories', 'ar' => 'الأقسام الفرعية'],
            'type'         => 'api',
            'api_method'   => 'categories',
            'filters'      => ['parent_id' => $category->id],
            'see_more'     => false,
            'manual_model' => 'category',
            'variant'      => VariantSection::Square->value,
        ]);

        PageSection::create([
            'name'            => ['en' => 'Subcategories', 'ar' => 'الأقسام الفرعية'],
            'page_id'         => $page->id,
            'section_id'      => $childrenSection->id,
            'display_type_id' => $categoryDisplayTypeId,
            'position'        => 'before',
            'variant'         => VariantSection::Square->value,
            'order'           => 1,
            'filters'         => ['parent_id' => $category->id],
            'is_active'       => true,
            'is_default'      => true,
        ]);

        $productsSection = Section::create([
            'name'         => ['en' => 'Products', 'ar' => 'المنتجات'],
            'type'         => 'api',
            'api_method'   => 'products',
            'filters'      => ['category_id' => $category->id],
            'see_more'     => true,
            'see_more_slug' => 'products',
            'details_slug' => 'product_details',
            'manual_model' => 'product',
            'variant'      => VariantSection::Vertical->value,
        ]);

        PageSection::create([
            'name'            => ['en' => 'Products', 'ar' => 'المنتجات'],
            'page_id'         => $page->id,
            'section_id'      => $productsSection->id,
            'display_type_id' => $productDisplayTypeId,
            'position'        => 'before',
            'variant'         => VariantSection::Vertical->value,
            'order'           => 2,
            'filters'         => ['category_id' => $category->id],
            'is_active'       => true,
            'is_default'      => true,
        ]);
    }

    private function resolveTitle(Category $category): string
    {
        $ar = $category->getTranslation('name', 'ar', false);
        $en = $category->getTranslation('name', 'en', false);

        return (string) ($ar ?: $en ?: $category->name ?: "Category #{$category->id}");
    }

    private function uniqueSlug(Category $category): string
    {
        return "category-{$category->id}";
    }
}
