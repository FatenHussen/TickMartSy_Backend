<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\Category\CategoryAttribute\UpdateRequest;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Services\Admin\CategoryAttributeService;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Tests\TestCase;

class AdminCategoryAttributeValueUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_renaming_values_by_id_keeps_the_same_ids_on_product_variants(): void
    {
        [$attribute, $small, $large, $variant] = $this->createSizeAttributeLinkedToProduct();

        app(CategoryAttributeService::class)->update($attribute->id, [
            'type' => 'square',
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'values' => [
                ['id' => $small->id, 'name' => ['en' => 'XS', 'ar' => 'XS']],
                ['id' => $large->id, 'name' => ['en' => 'XL', 'ar' => 'XL']],
            ],
        ]);

        $this->assertSame($small->id, $small->fresh()->id);
        $this->assertSame('XS', $small->fresh()->getTranslation('name', 'en'));
        $this->assertSame('XL', $large->fresh()->getTranslation('name', 'en'));
        $this->assertSame(
            [$small->id, $large->id],
            $variant->fresh()->attributes_values_ids
        );
        $this->assertEquals(2, AttributeValue::where('category_attribute_id', $attribute->id)->count());
    }

    public function test_renaming_values_without_ids_updates_existing_rows_in_order(): void
    {
        [$attribute, $small, $large, $variant] = $this->createSizeAttributeLinkedToProduct();

        app(CategoryAttributeService::class)->update($attribute->id, [
            'type' => 'square',
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'values' => [
                ['name' => ['en' => 'XS', 'ar' => 'XS']],
                ['name' => ['en' => 'XL', 'ar' => 'XL']],
            ],
        ]);

        $this->assertSame('XS', $small->fresh()->getTranslation('name', 'en'));
        $this->assertSame('XL', $large->fresh()->getTranslation('name', 'en'));
        $this->assertSame(
            [$small->id, $large->id],
            $variant->fresh()->attributes_values_ids
        );
    }

    public function test_product_attributes_stay_separate_after_renaming_size(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        $colorAttr = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $sizeAttr = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $red = AttributeValue::create([
            'category_attribute_id' => $colorAttr->id,
            'name' => ['en' => 'Red', 'ar' => 'أحمر'],
        ]);

        $small = AttributeValue::create([
            'category_attribute_id' => $sizeAttr->id,
            'name' => ['en' => 'Small', 'ar' => 'صغير'],
        ]);

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

        $product = Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'sku' => 'PARENT-SKU',
            'price' => 20,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $variant = $product->variants()->create([
            'sku' => 'SKU-RED-S',
            'price' => 20,
            'is_active' => true,
            'attributes_values_ids' => [$red->id, $small->id],
        ]);

        app(CategoryAttributeService::class)->update($sizeAttr->id, [
            'type' => 'square',
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'values' => [
                ['id' => $small->id, 'name' => ['en' => 'XS', 'ar' => 'XS']],
            ],
        ]);

        $attributes = collect($variant->fresh()->getAttributesWithDetails());

        $this->assertCount(2, $attributes);
        $this->assertTrue($attributes->contains(fn ($attr) => $attr['id'] === $red->id));
        $this->assertTrue($attributes->contains(fn ($attr) => $attr['id'] === $small->id));
        $this->assertTrue($attributes->contains(
            fn ($attr) => $attr['id'] === $red->id && $attr['category_attribute']['id'] === $colorAttr->id
        ));
        $this->assertTrue($attributes->contains(
            fn ($attr) => $attr['id'] === $small->id && $attr['category_attribute']['id'] === $sizeAttr->id
        ));
        $this->assertSame('XS', $small->fresh()->getTranslation('name', 'ar'));
        $this->assertFalse($attributes->contains(fn ($attr) => $attr['name'] === 'أحمر XS'));
    }

    public function test_updating_product_with_only_color_id_keeps_existing_size_id(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        $colorAttr = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Color', 'ar' => 'اللون'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $sizeAttr = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $red = AttributeValue::create([
            'category_attribute_id' => $colorAttr->id,
            'name' => ['en' => 'Red', 'ar' => 'أحمر'],
        ]);

        $small = AttributeValue::create([
            'category_attribute_id' => $sizeAttr->id,
            'name' => ['en' => 'Small', 'ar' => 'صغير'],
        ]);

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

        $product = Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'sku' => 'PARENT-SKU',
            'price' => 20,
            'is_active' => true,
            'is_visible' => true,
        ]);

        $variant = $product->variants()->create([
            'sku' => 'SKU-RED-S',
            'price' => 20,
            'is_active' => true,
            'attributes_values_ids' => [$red->id, $small->id],
        ]);

        app(ProductService::class)->update($product->id, [
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => 20,
                    'is_active' => true,
                    'attributes_values_ids' => [$red->id],
                ],
            ],
        ]);

        $ids = $variant->fresh()->attributes_values_ids;
        sort($ids);

        $this->assertSame([$red->id, $small->id], $ids);
    }

    public function test_update_request_keeps_value_ids(): void
    {
        $this->seedLanguages();

        $category = Category::create([
            'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        $attribute = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $value = AttributeValue::create([
            'category_attribute_id' => $attribute->id,
            'name' => ['en' => 'Small', 'ar' => 'صغير'],
        ]);

        $request = UpdateRequest::create(
            "/api/admin/category-attributes/{$attribute->id}",
            'PUT',
            [
                'type' => 'square',
                'name' => ['en' => 'Size', 'ar' => 'القياس'],
                'values' => [
                    [
                        'id' => $value->id,
                        'name' => ['en' => 'XS', 'ar' => 'XS'],
                    ],
                ],
            ]
        );
        $request->setContainer(app());
        $request->setRedirector(app(Redirector::class));
        $request->setRouteResolver(function () use ($attribute) {
            $route = new \Illuminate\Routing\Route('PUT', 'category-attributes/{category_attribute}', []);
            $route->bind(request());
            $route->setParameter('category_attribute', $attribute->id);

            return $route;
        });

        $request->validateResolved();

        $this->assertSame($value->id, $request->validated()['values'][0]['id']);
        $this->assertSame('XS', $request->validated()['values'][0]['name']['en']);
    }

    /**
     * @return array{0: CategoryAttribute, 1: AttributeValue, 2: AttributeValue, 3: ProductVariant}
     */
    private function createSizeAttributeLinkedToProduct(): array
    {
        $category = Category::create([
            'name' => ['en' => 'Fashion', 'ar' => 'أزياء'],
            'is_active' => true,
            'is_restaurant' => false,
        ]);

        $attribute = CategoryAttribute::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Size', 'ar' => 'القياس'],
            'type' => 'square',
            'is_active' => true,
        ]);

        $small = AttributeValue::create([
            'category_attribute_id' => $attribute->id,
            'name' => ['en' => 'Small', 'ar' => 'صغير'],
        ]);

        $large = AttributeValue::create([
            'category_attribute_id' => $attribute->id,
            'name' => ['en' => 'Large', 'ar' => 'كبير'],
        ]);

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

        $product = Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Jeans', 'ar' => 'جينز'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PARENT-SKU',
            'price' => 20,
            'quantity' => 10,
            'approval_status' => 'approved',
            'is_active' => true,
            'is_visible' => true,
        ]);

        $variant = $product->variants()->create([
            'sku' => 'SKU-SIZE',
            'price' => 20,
            'quantity' => 10,
            'is_active' => true,
            'attributes_values_ids' => [$small->id, $large->id],
        ]);

        return [$attribute, $small, $large, $variant];
    }

    private function seedLanguages(): void
    {
        Language::factory()->arabic()->create(['is_active' => true]);
        Language::factory()->english()->create(['is_active' => true, 'is_default' => false]);
    }
}
