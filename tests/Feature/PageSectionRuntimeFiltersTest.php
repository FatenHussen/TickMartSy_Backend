<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\Section;
use App\Models\Vendor;
use App\Models\Category;
use App\Support\DisplayTypeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSectionRuntimeFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DisplayTypeSeeder::class);
    }

    public function test_manual_sections_ignore_url_filters(): void
    {
        $page = Page::create(['title' => 'Products', 'slug' => 'products-filter-test']);

        $bannerSection = Section::create([
            'name' => ['en' => 'Banners', 'ar' => 'بانرات'],
            'type' => 'manual',
            'manual_model' => 'banner',
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $bannerSection->id,
            'display_type_id' => DisplayTypeCatalog::idFor('banner'),
            'position' => 'before',
            'order' => 1,
            'is_active' => true,
        ]);

        $withoutFilters = $this->getJson('/api/user/sections?page_slug=products-filter-test');
        $withFilters = $this->getJson('/api/user/sections?page_slug=products-filter-test&on_sale=1&category_id=99');

        $withoutFilters->assertOk();
        $withFilters->assertOk();

        $this->assertSame(
            $withoutFilters->json('data.0.items'),
            $withFilters->json('data.0.items'),
        );
        $this->assertSame('manual', $withFilters->json('data.0.type'));
        $this->assertSame('banner', $withFilters->json('data.0.manual_model'));
        $this->assertNull($withFilters->json('data.0.api_method'));
    }

    public function test_api_product_sections_merge_url_filters_over_section_baseline(): void
    {
        $page = Page::create(['title' => 'Products', 'slug' => 'products-api-filter-test']);
        $category = Category::create([
            'name' => ['en' => 'Cat', 'ar' => 'Cat'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);
        $vendor = Vendor::create([
            'name' => ['en' => 'Vendor', 'ar' => 'Vendor'],
            'owner_name' => 'Owner',
            'owner_phone' => '0500000001',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-1',
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);

        $onSaleProduct = $this->createProduct($category, $vendor, 'Sale product', 20);
        $this->createProduct($category, $vendor, 'Regular product', 0);

        $productsSection = Section::create([
            'name' => ['en' => 'Products', 'ar' => 'Products'],
            'type' => 'api',
            'api_method' => 'products',
            'manual_model' => 'product',
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $productsSection->id,
            'display_type_id' => DisplayTypeCatalog::idFor('product'),
            'position' => 'after',
            'order' => 1,
            'filters' => ['category_id' => $category->id],
            'is_active' => true,
        ]);

        $allResponse = $this->getJson("/api/user/sections?page_slug=products-api-filter-test&category_id={$category->id}");
        $saleResponse = $this->getJson("/api/user/sections?page_slug=products-api-filter-test&category_id={$category->id}&on_sale=1");

        $allResponse->assertOk();
        $saleResponse->assertOk();

        $allIds = collect($allResponse->json('data.0.items'))->pluck('id');
        $saleIds = collect($saleResponse->json('data.0.items'))->pluck('id');

        $this->assertGreaterThan($saleIds->count(), $allIds->count());
        $this->assertTrue($saleIds->contains($onSaleProduct->id));
        $this->assertSame('api', $saleResponse->json('data.0.type'));
        $this->assertSame('products', $saleResponse->json('data.0.api_method'));
        $this->assertNull($saleResponse->json('data.0.manual_model'));
        $this->assertSame('product', $saleResponse->json('data.0.content_type'));
    }

    public function test_inactive_page_sections_are_hidden_from_user_api(): void
    {
        $page = Page::create(['title' => 'Home', 'slug' => 'home-visibility-test']);

        $visibleSection = Section::create([
            'name' => ['en' => 'Visible', 'ar' => 'Visible'],
            'type' => 'manual',
            'manual_model' => 'banner',
            'is_active' => true,
        ]);

        $hiddenSection = Section::create([
            'name' => ['en' => 'Hidden', 'ar' => 'Hidden'],
            'type' => 'manual',
            'manual_model' => 'banner',
            'is_active' => true,
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $visibleSection->id,
            'display_type_id' => DisplayTypeCatalog::idFor('banner'),
            'position' => 'before',
            'order' => 1,
            'is_active' => true,
        ]);

        PageSection::create([
            'page_id' => $page->id,
            'section_id' => $hiddenSection->id,
            'display_type_id' => DisplayTypeCatalog::idFor('banner'),
            'position' => 'after',
            'order' => 2,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/user/sections?page_slug=home-visibility-test');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame(1, $response->json('data.0.order'));
    }

    private function createProduct(Category $category, Vendor $vendor, string $name, int $discount): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => $name, 'ar' => $name],
            'description' => ['en' => $name, 'ar' => $name],
            'discount' => $discount,
            'is_active' => true,
            'approval_status' => \App\Enums\ProductApprovalStatus::APPROVED->value,
        ]);
    }
}
