<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Tests\TestCase;

class AdminProductStoreVendorTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_request_accepts_platform_product_without_vendor_id(): void
    {
        $this->seedLanguages();
        $vendor = $this->createVendor();
        $category = $this->createCategory();

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
        ]);

        $this->assertSame($vendor->id, (int) $request->input('vendor_id'));
    }

    public function test_store_request_ignores_missing_vendor_one_and_uses_existing_vendor(): void
    {
        $this->seedLanguages();
        $first = $this->createVendor();
        $first->forceDelete();
        $vendor = $this->createVendor();
        $this->assertNotSame(ProductService::PLATFORM_VENDOR_ID, $vendor->id);

        $category = $this->createCategory();

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
            'vendor_id' => 0,
        ]);

        $this->assertSame($vendor->id, (int) $request->input('vendor_id'));
    }

    public function test_store_request_uses_vendor_user_vendor_id_not_user_id(): void
    {
        $this->seedLanguages();
        $this->createVendor();
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Branch', 'ar' => 'فرع'],
            'email' => 'branch-vendor@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_default' => true,
        ]);

        $vendorUser = VendorUser::create([
            'name' => 'Vendor Admin',
            'email' => 'vendor-admin@example.com',
            'password' => 'password',
            'is_active' => true,
            'vendor_id' => $vendor->id,
        ]);

        $this->assertNotSame($vendorUser->id, $vendor->id);
        $this->actingAs($vendorUser, 'vendor-user');

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Vendor product', 'ar' => 'منتج بائع'],
            'shop_variants' => [
                [
                    'shop_id' => $shop->id,
                    'variant_index' => 0,
                ],
            ],
        ]);

        $this->assertSame('shop', $request->input('sale_channel'));
        $this->assertSame($vendor->id, (int) $request->input('vendor_id'));
    }

    public function test_store_request_takes_shop_vendor_and_drops_invalid_client_vendor_id(): void
    {
        $this->seedLanguages();
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Branch', 'ar' => 'فرع'],
            'email' => 'branch@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_default' => true,
        ]);

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'shop',
            'vendor_id' => 999999,
            'name' => ['en' => 'Shop product', 'ar' => 'منتج متجر'],
            'shop_variants' => [
                [
                    'shop_id' => $shop->id,
                    'variant_index' => 0,
                ],
            ],
        ]);

        $this->assertSame($vendor->id, (int) $request->input('vendor_id'));
    }

    public function test_product_service_creates_platform_product_when_vendor_one_is_missing(): void
    {
        $first = $this->createVendor();
        $first->forceDelete();
        $vendor = $this->createVendor();
        $category = $this->createCategory();

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 10,
            'quantity' => 1,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);

        $product = Product::findOrFail($resource->id);
        $this->assertSame($vendor->id, (int) $product->vendor_id);
        $this->assertSame('platform', $product->sale_channel);
    }

    private function validateStore(array $payload): StoreRequest
    {
        $request = StoreRequest::create('/api/admin/products', 'POST', $payload);
        $request->headers->set('Accept', 'application/json');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->validateResolved();

        return $request;
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
