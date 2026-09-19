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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminProductStoreVendorTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_request_accepts_platform_product_without_vendor_id(): void
    {
        $this->seedLanguages();
        $this->createVendor();
        $category = $this->createCategory();

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
        ]);

        $this->assertSame(ProductService::resolvePlatformVendorId(), (int) $request->input('vendor_id'));
    }

    public function test_store_request_ignores_missing_vendor_one_and_uses_existing_vendor(): void
    {
        $this->seedLanguages();
        Vendor::query()->whereKey(ProductService::PLATFORM_VENDOR_ID)->forceDelete();
        Shop::query()->where('vendor_id', ProductService::PLATFORM_VENDOR_ID)->forceDelete();
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
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store-vendor@example.com',
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
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store@example.com',
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
        Vendor::query()->whereKey(ProductService::PLATFORM_VENDOR_ID)->forceDelete();
        Shop::query()->where('vendor_id', ProductService::PLATFORM_VENDOR_ID)->forceDelete();
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

    public function test_product_service_creates_without_description_and_skips_empty_category_details(): void
    {
        $this->createVendor();
        $category = $this->createCategory();

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
            'price' => 10,
            'quantity' => 1,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'category_details' => [
                ['detail_value' => ['ar' => null, 'en' => null]],
                ['category_detail_id' => null, 'detail_value' => ['ar' => 'x']],
            ],
            'extra_details' => [
                [],
            ],
            'time_prepare' => 'فوري',
        ]);

        $product = Product::with('categoryDetails', 'variants')->findOrFail($resource->id);
        $this->assertSame(ProductService::resolvePlatformVendorId(), (int) $product->vendor_id);
        $this->assertSame(0, $product->categoryDetails->count());
        $this->assertNotNull($product->variants->first());
        $this->assertNull($product->time_prepare);

        $payload = $resource->toArray(request());
        $this->assertSame($product->id, $payload['id']);
        $this->assertSame('منتج الموقع', $payload['name']['ar'] ?? $payload['name']);
    }

    public function test_store_request_accepts_empty_extra_and_category_detail_rows(): void
    {
        $this->seedLanguages();
        $this->createVendor();
        $category = $this->createCategory();

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
            'category_details' => [
                ['detail_value' => ['ar' => null, 'en' => null]],
            ],
            'extra_details' => [
                [],
            ],
        ]);

        $this->assertSame($category->id, (int) $request->input('category_id'));
    }

    public function test_store_request_accepts_fixed_discount_decimals_and_values_over_100(): void
    {
        $this->seedLanguages();
        $this->createVendor();
        $category = $this->createCategory();

        $request = $this->validateStore([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
            'price' => 200,
            'discount_type' => 'fixed',
            'discount' => 150.75,
            'variants' => [
                [
                    'price' => 200,
                    'discount_type' => 'fixed',
                    'discount' => 120.5,
                ],
            ],
        ]);

        $this->assertEquals(150.75, (float) $request->input('discount'));
        $this->assertEquals(120.5, (float) data_get($request->input('variants'), '0.discount'));
    }

    public function test_store_request_rejects_percentage_discount_over_100(): void
    {
        $this->seedLanguages();
        $this->createVendor();
        $category = $this->createCategory();

        try {
            $this->validateStore([
                'category_id' => $category->id,
                'sale_channel' => 'platform',
                'name' => ['en' => 'Site product', 'ar' => 'منتج الموقع'],
                'discount_type' => 'percentage',
                'discount' => 150,
            ]);
            $this->fail('Percentage discount above 100 should fail validation.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('discount', $e->errors());
        }
    }

    public function test_create_persists_fixed_discount_decimals_over_100(): void
    {
        $this->seedLanguages();
        $this->createVendor();
        $category = $this->createCategory();

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Fixed discount', 'ar' => 'خصم ثابت'],
            'price' => 200,
            'discount_type' => 'fixed',
            'discount' => 150.75,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'variants' => [
                [
                    'price' => 200,
                    'discount_type' => 'fixed',
                    'discount' => 120.5,
                ],
            ],
        ]);

        $product = Product::with('variants')->findOrFail($resource->id);

        $this->assertSame('fixed', $product->discount_type);
        $this->assertEquals(150.75, (float) $product->discount);
        $this->assertEquals(120.5, (float) $product->variants->first()->discount);
        $this->assertEquals(49.25, (float) $product->price_after_discount);
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
