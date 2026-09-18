<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\Vendor;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductVariantImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_product_without_image_fields_keeps_variant_images(): void
    {
        Storage::fake('public');

        $product = $this->createProductWithVariant();
        $variant = $product->variants()->first();
        $media = $variant->media()->create([
            'collection' => ProductMedia::COLLECTION_VARIANT,
            'path' => 'product-variant/variant/keep.png',
            'order' => 0,
        ]);

        app(ProductService::class)->update($product->id, [
            'price' => 22,
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => 22,
                    'quantity' => 10,
                    'is_active' => true,
                    'attributes_values_ids' => [],
                ],
            ],
        ]);

        $this->assertDatabaseHas('product_media', [
            'id' => $media->id,
            'mediable_id' => $variant->id,
            'mediable_type' => $variant->getMorphClass(),
        ]);
    }

    public function test_updating_product_uploads_variant_images_and_user_api_returns_them(): void
    {
        Storage::fake('public');

        $product = $this->createProductWithVariant();
        $variant = $product->variants()->first();
        $file = UploadedFile::fake()->image('black.jpg');

        app(ProductService::class)->update($product->id, [
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => 20,
                    'quantity' => 10,
                    'is_active' => true,
                    'attributes_values_ids' => [],
                    'images' => [$file],
                ],
            ],
        ]);

        $variant->refresh();
        $this->assertCount(1, $variant->media);
        $this->assertSame(ProductMedia::COLLECTION_VARIANT, $variant->media->first()->collection);

        $response = $this->getJson("/api/user/products/{$product->id}");
        $response->assertOk();
        $images = $response->json('data.shop_variants.0.images');
        $this->assertIsArray($images);
        $this->assertNotEmpty($images);
        $this->assertNotNull($images[0]['path'] ?? null);
        $this->assertTrue($response->json('data.shop_variants.0.has_variant_images'));
    }

    public function test_user_api_falls_back_to_product_images_when_variant_has_none(): void
    {
        $product = $this->createProductWithVariant();
        $product->media()->create([
            'collection' => ProductMedia::COLLECTION_PRODUCT,
            'path' => 'product/product/main.png',
            'order' => 0,
        ]);

        $response = $this->getJson("/api/user/products/{$product->id}");
        $response->assertOk();

        $this->assertFalse($response->json('data.shop_variants.0.has_variant_images'));
        $this->assertNotEmpty($response->json('data.images'));
        $this->assertSame(
            $response->json('data.images.0.path'),
            $response->json('data.shop_variants.0.images.0.path')
        );
    }

    public function test_update_request_keeps_variant_image_files_in_validated_payload(): void
    {
        $this->seedLanguages();
        $product = $this->createProductWithVariant();
        $file = UploadedFile::fake()->image('red.png');

        $request = UpdateRequest::create(
            "/api/admin/products/{$product->id}",
            'POST',
            [
                'variants' => [
                    [
                        'id' => $product->variants()->first()->id,
                        'sku' => 'SKU-KEEP',
                        'price' => 20,
                        'quantity' => 4,
                    ],
                ],
            ],
            [],
            [
                'variants' => [
                    [
                        'images' => [$file],
                    ],
                ],
            ]
        );
        $request->headers->set('Accept', 'application/json');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->setRouteResolver(function () use ($product, $request) {
            $route = new \Illuminate\Routing\Route('POST', 'products/{product}', []);
            $route->bind($request);
            $route->setParameter('product', $product);

            return $route;
        });
        $request->validateResolved();

        $validated = $request->validated();
        $this->assertNotEmpty($validated['variants'][0]['images'] ?? null);
        $this->assertInstanceOf(UploadedFile::class, $validated['variants'][0]['images'][0]);
    }

    private function createProductWithVariant(): Product
    {
        $vendor = Vendor::create([
            'name' => ['en' => 'Tikmool', 'ar' => 'تيكموول'],
            'owner_name' => 'Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-' . str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Street jeans', 'ar' => 'بنطال جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PARENT-SKU',
            'price' => 20,
            'quantity' => 10,
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $product->variants()->create([
            'sku' => 'SKU-GD719HTA',
            'price' => 20,
            'quantity' => 10,
            'is_active' => true,
            'attributes_values_ids' => [],
        ]);

        return $product->refresh();
    }

    private function seedLanguages(): void
    {
        Language::factory()->arabic()->create(['is_active' => true]);
        Language::factory()->english()->create(['is_active' => true, 'is_default' => false]);
    }
}
