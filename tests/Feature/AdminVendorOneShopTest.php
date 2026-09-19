<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use App\Services\Admin\ShopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminVendorOneShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_cannot_have_a_second_shop(): void
    {
        $vendor = $this->createVendor();
        Shop::create([
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(ShopService::class)->create([
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Second', 'ar' => 'ثاني'],
            'email' => 'second@example.com',
        ]);
    }

    public function test_shop_product_links_all_variants_when_only_shop_id_is_sent(): void
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store-product@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'shop',
            'shop_id' => $shop->id,
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 20,
            'quantity' => 4,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'variants' => [
                ['sku' => 'FIRST', 'price' => 20, 'quantity' => 4, 'is_active' => true],
                ['sku' => 'SECOND', 'is_active' => true],
            ],
        ]);

        $product = Product::with('variants.shopVariants')->findOrFail($resource->id);

        $this->assertSame($vendor->id, (int) $product->vendor_id);
        $this->assertCount(2, $product->variants);
        foreach ($product->variants as $variant) {
            $this->assertCount(1, $variant->shopVariants);
            $this->assertSame($shop->id, $variant->shopVariants->first()->shop_id);
        }
    }

    public function test_store_request_accepts_shop_channel_with_shop_id_only(): void
    {
        $this->seedLanguages();
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store-request@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $request = StoreRequest::create('/api/admin/products', 'POST', [
            'category_id' => $category->id,
            'sale_channel' => 'shop',
            'shop_id' => $shop->id,
            'name' => ['en' => 'Shop product', 'ar' => 'منتج متجر'],
        ]);
        $request->headers->set('Accept', 'application/json');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->validateResolved();

        $this->assertSame($vendor->id, (int) $request->input('vendor_id'));
    }

    private function seedLanguages(): void
    {
        Language::factory()->arabic()->create(['is_active' => true]);
        Language::factory()->english()->create(['is_active' => true, 'is_default' => false]);
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
