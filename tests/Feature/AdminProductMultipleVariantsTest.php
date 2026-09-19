<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Color;
use App\Models\Language;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Tests\TestCase;

class AdminProductMultipleVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_two_variants_links_both_to_the_same_shop_and_returns_them_on_the_site(): void
    {
        $platformVendor = $this->createVendor();
        $category = $this->createCategory();
        Shop::create([
            'name' => ['en' => 'Platform default', 'ar' => 'فرع المنصة'],
            'email' => 'platform-default@example.com',
            'vendor_id' => $platformVendor->id,
            'is_active' => true,
            'is_default' => true,
        ]);

        [$blue, $black, $small, $large] = $this->createColorAndSizeValues($category);

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'platform',
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'JEANS-PARENT',
            'price' => 25,
            'quantity' => 10,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);

        $product = Product::findOrFail($resource->id);

        app(ProductService::class)->update($product->id, [
            'sale_channel' => 'platform',
            'variants' => [
                [
                    'sku' => 'JEANS-BLUE-S',
                    'price' => 25,
                    'attributes_values_ids' => [$blue->id, $small->id],
                ],
                [
                    'sku' => 'JEANS-BLACK-L',
                    'attributes' => [
                        ['id' => $black->id],
                        ['id' => $large->id],
                    ],
                ],
            ],
        ]);

        $product->refresh()->load('variants.shopVariants');

        $this->assertCount(2, $product->variants);
        $linkedShopId = $product->variants->first()?->shopVariants->first()?->shop_id;
        $this->assertNotNull($linkedShopId);
        foreach ($product->variants as $variant) {
            $this->assertTrue($variant->is_active);
            $this->assertSame(10, $variant->quantity);
            $this->assertCount(1, $variant->shopVariants);
            $this->assertSame($linkedShopId, $variant->shopVariants->first()->shop_id);
        }

        $this->assertEqualsCanonicalizing(
            [$blue->id, $small->id],
            $product->variants->firstWhere('sku', 'JEANS-BLUE-S')->attributes_values_ids
        );
        $this->assertEqualsCanonicalizing(
            [$black->id, $large->id],
            $product->variants->firstWhere('sku', 'JEANS-BLACK-L')->attributes_values_ids
        );

        $response = $this->withHeaders(['Accept-Language' => 'ar'])
            ->getJson("/api/user/products/{$product->id}");

        $response->assertOk();
        $shopVariants = $response->json('data.shop_variants');
        $this->assertCount(2, $shopVariants);
        $this->assertNotNull($shopVariants[0]['id']);
        $this->assertNotNull($shopVariants[1]['id']);
        $this->assertNotSame($shopVariants[0]['id'], $shopVariants[1]['id']);

        $bySku = collect($shopVariants)->keyBy('sku');
        $this->assertSame(
            $product->variants->firstWhere('sku', 'JEANS-BLUE-S')->id,
            $bySku['JEANS-BLUE-S']['variant_id']
        );
        $this->assertSame(
            $product->variants->firstWhere('sku', 'JEANS-BLACK-L')->id,
            $bySku['JEANS-BLACK-L']['variant_id']
        );
        $this->assertSame('#000000', collect($bySku['JEANS-BLACK-L']['attributes'])->firstWhere('type', 'color')['hex']);

        $mapValues = collect($response->json('data.attributes_map'))
            ->mapWithKeys(fn ($row) => [$row['attribute'] => $row['values']]);

        $this->assertEqualsCanonicalizing(['أزرق', 'أسود'], $mapValues['لون']);
        $this->assertEqualsCanonicalizing(['S', 'L'], $mapValues['قياس']);
    }

    public function test_shop_channel_copies_store_to_the_second_variant(): void
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = Shop::create([
            'name' => ['en' => 'Store', 'ar' => 'متجر'],
            'email' => 'store@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $resource = app(ProductService::class)->create([
            'category_id' => $category->id,
            'sale_channel' => 'shop',
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 20,
            'quantity' => 4,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'variants' => [
                [
                    'sku' => 'FIRST',
                    'price' => 20,
                    'quantity' => 4,
                    'is_active' => true,
                    'attributes_values_ids' => [],
                ],
                [
                    'sku' => 'SECOND',
                    'is_active' => true,
                    'attributes_values_ids' => [],
                ],
            ],
            'shop_variants' => [
                [
                    'shop_id' => $shop->id,
                    'variant_index' => 0,
                    'cost_price' => 8,
                ],
            ],
        ]);

        $product = Product::with('variants.shopVariants')->findOrFail($resource->id);

        $this->assertCount(2, $product->variants);
        foreach ($product->variants as $variant) {
            $this->assertCount(1, $variant->shopVariants);
            $this->assertSame($shop->id, $variant->shopVariants->first()->shop_id);
        }

        $response = $this->getJson("/api/user/products/{$product->id}");
        $response->assertOk();
        $this->assertCount(2, $response->json('data.shop_variants'));
        $this->assertNotNull($response->json('data.shop_variants.0.id'));
        $this->assertNotNull($response->json('data.shop_variants.1.id'));
    }

    public function test_update_request_copies_attribute_ids_from_attributes_objects(): void
    {
        $this->seedLanguages();
        $category = $this->createCategory();
        $value = AttributeValue::create([
            'category_attribute_id' => CategoryAttribute::create([
                'category_id' => $category->id,
                'name' => ['en' => 'Size', 'ar' => 'قياس'],
                'type' => 'square',
                'is_active' => true,
            ])->id,
            'name' => ['en' => 'S', 'ar' => 'S'],
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'vendor_id' => $this->createVendor()->id,
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'price' => 20,
            'quantity' => 1,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $request = UpdateRequest::create(
            "/api/admin/products/{$product->id}",
            'POST',
            [
                'variants' => [
                    [
                        'attributes' => [
                            ['id' => $value->id, 'value' => 'S'],
                        ],
                    ],
                ],
            ]
        );
        $request->headers->set('Accept', 'application/json');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->setRouteResolver(function () use ($product) {
            $route = new \Illuminate\Routing\Route('PUT', 'products/{product}', []);
            $route->bind(request());
            $route->setParameter('product', $product->id);

            return $route;
        });

        $request->validateResolved();

        $this->assertSame([$value->id], $request->validated()['variants'][0]['attributes_values_ids']);
    }

    /**
     * @return array{0: AttributeValue, 1: AttributeValue, 2: AttributeValue, 3: AttributeValue}
     */
    private function createColorAndSizeValues(Category $category): array
    {
        $colorAttribute = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Color', 'ar' => 'لون'],
            'type' => 'color',
            'is_active' => true,
        ]);

        $blueColor = Color::create([
            'name' => ['en' => 'Blue', 'ar' => 'أزرق'],
            'hex' => '#0000FF',
            'is_active' => true,
        ]);
        $blackColor = Color::create([
            'name' => ['en' => 'Black', 'ar' => 'أسود'],
            'hex' => '#000000',
            'is_active' => true,
        ]);

        $blue = AttributeValue::create([
            'category_attribute_id' => $colorAttribute->id,
            'name' => ['en' => 'Blue', 'ar' => 'أزرق'],
            'color_id' => $blueColor->id,
        ]);
        $black = AttributeValue::create([
            'category_attribute_id' => $colorAttribute->id,
            'name' => ['en' => 'Black', 'ar' => 'أسود'],
            'color_id' => $blackColor->id,
        ]);

        $sizeAttribute = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Size', 'ar' => 'قياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $small = AttributeValue::create([
            'category_attribute_id' => $sizeAttribute->id,
            'name' => ['en' => 'S', 'ar' => 'S'],
        ]);
        $large = AttributeValue::create([
            'category_attribute_id' => $sizeAttribute->id,
            'name' => ['en' => 'L', 'ar' => 'L'],
        ]);

        return [$blue, $black, $small, $large];
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
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

    private function seedLanguages(): void
    {
        Language::factory()->arabic()->create(['is_active' => true]);
        Language::factory()->english()->create(['is_active' => true, 'is_default' => false]);
    }
}
