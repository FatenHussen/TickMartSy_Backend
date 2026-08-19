<?php

namespace Tests\Feature;

use App\Enums\VariantSection;
use App\Models\Category;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\Section;
use App\Models\Vendor;
use App\Services\Admin\CategoryPageService;
use App\Support\DisplayTypeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DisplayTypeSeeder::class);
    }

    public function test_category_page_sections_expose_discriminators_and_is_default(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Clothing', 'ar' => 'ملابس'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        $page = app(CategoryPageService::class)->syncForCategory($category);

        $adminProductSection = Section::create([
            'name' => ['en' => 'Best sellers', 'ar' => 'الأكثر مبيعاً'],
            'type' => 'api',
            'api_method' => 'products',
            'manual_model' => 'product',
            'see_more' => false,
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $adminProductSection->id,
            'display_type_id' => DisplayTypeCatalog::idFor('product'),
            'position' => 'after',
            'order' => 4,
            'filters' => ['type' => 'top_rated'],
            'is_active' => true,
            'is_default' => false,
        ]);

        $response = $this->getJson("/api/user/categories/{$category->id}/page");

        $response->assertOk()
            ->assertJsonPath('status', true);

        $sections = collect($response->json('data.sections'));

        $this->assertCount(3, $sections);

        $defaults = $sections->where('is_default', true)->values();
        $this->assertCount(2, $defaults);
        $this->assertSame(['category', 'product'], $defaults->pluck('content_type')->all());
        $this->assertSame(['categories', 'products'], $defaults->pluck('api_method')->all());
        $this->assertContains(null, $defaults->pluck('manual_model')->all());

        $subcategories = $defaults->firstWhere('api_method', 'categories');
        $this->assertSame(DisplayTypeCatalog::idFor('category'), $subcategories['display_type_id']);
        $this->assertNull($subcategories['manual_model']);

        $adminSection = $sections->firstWhere('order', 4);
        $this->assertNotNull($adminSection);
        $this->assertFalse($adminSection['is_default']);
        $this->assertSame('product', $adminSection['content_type']);
        $this->assertNull($adminSection['manual_model']);
        $this->assertSame('products', $adminSection['api_method']);

        foreach ($sections as $section) {
            $this->assertArrayHasKey('content_type', $section);
            $this->assertArrayHasKey('manual_model', $section);
            $this->assertArrayHasKey('api_method', $section);
            $this->assertArrayHasKey('is_default', $section);
            $this->assertArrayHasKey('display_type_id', $section);
        }
    }

    public function test_category_subcategory_items_use_absolute_image_urls(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Root', 'ar' => 'جذر'],
            'icon' => 'categories/foo.png',
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        Category::create([
            'name' => ['en' => 'Child', 'ar' => 'فرع'],
            'icon' => 'categories/child.png',
            'parent_id' => $category->id,
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        app(CategoryPageService::class)->syncForCategory($category);

        $response = $this->getJson("/api/user/categories/{$category->id}/page");
        $response->assertOk();

        $subcategories = collect($response->json('data.sections'))
            ->firstWhere('api_method', 'categories');

        $this->assertNotNull($subcategories);
        $this->assertNotEmpty($subcategories['items']);

        $image = $subcategories['items'][0]['image'] ?? null;
        $this->assertIsString($image);
        $this->assertStringContainsString('storage/categories/child.png', $image);
        $this->assertStringStartsWith('http', $image);
    }

    public function test_multiple_banner_sections_are_not_merged(): void
    {
        $page = Page::create([
            'title' => 'Home',
            'slug' => 'home-test-banners',
        ]);

        $bannerSectionA = Section::create([
            'name' => ['en' => 'Banner A', 'ar' => 'بانر أ'],
            'type' => 'manual',
            'manual_model' => 'banner',
        ]);

        $bannerSectionB = Section::create([
            'name' => ['en' => 'Banner B', 'ar' => 'بانر ب'],
            'type' => 'manual',
            'manual_model' => 'banner',
        ]);

        foreach ([$bannerSectionA, $bannerSectionB] as $index => $section) {
            PageSection::create([
                'page_id' => $page->id,
                'section_id' => $section->id,
                'display_type_id' => DisplayTypeCatalog::idFor('banner'),
                'position' => 'before',
                'order' => $index + 1,
                'variant' => VariantSection::Horizontal->value,
                'is_active' => true,
            ]);
        }

        $response = $this->getJson('/api/user/sections?page_slug=home-test-banners');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }
}
