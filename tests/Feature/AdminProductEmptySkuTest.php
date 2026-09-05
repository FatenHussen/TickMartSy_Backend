<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductEmptySkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_sku_and_model_are_stored_as_null_and_do_not_collide(): void
    {
        $category = $this->createCategory();
        $this->createVendor();

        $first = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'First', 'ar' => 'أول'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => '',
            'model' => '',
            'barcode' => '',
            'price' => 10,
            'quantity' => 1,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);

        $second = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Second', 'ar' => 'ثاني'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => '   ',
            'model' => '',
            'barcode' => '',
            'price' => 12,
            'quantity' => 2,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);

        $firstProduct = Product::with('variants')->findOrFail($first->id);
        $secondProduct = Product::with('variants')->findOrFail($second->id);

        $this->assertNull($firstProduct->sku);
        $this->assertNull($firstProduct->model);
        $this->assertNull($firstProduct->barcode);
        $this->assertNull($secondProduct->sku);
        $this->assertNull($secondProduct->model);
        $this->assertNull($secondProduct->barcode);
        $this->assertNull($firstProduct->variants->first()?->sku);
        $this->assertNull($secondProduct->variants->first()?->sku);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);
    }

    private function createVendor(): Vendor
    {
        return Vendor::create([
            'name' => ['en' => 'Tikmool', 'ar' => 'تيكموول'],
            'owner_name' => 'Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-' . str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);
    }
}
