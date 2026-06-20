<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\Vendor;
use App\Services\User\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionTargetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_product_eligible_respects_shop_targeting(): void
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $targetShop = $this->createShop($vendor, 'Target Shop');
        $otherShop = $this->createShop($vendor, 'Other Shop');

        $targetProduct = $this->createProduct($category, $vendor, 'Target Product');
        $otherProduct = $this->createProduct($category, $vendor, 'Other Product');

        $this->createVariantWithShopVariant($targetProduct, $targetShop);
        $this->createVariantWithShopVariant($otherProduct, $otherShop);

        $promotion = $this->createPromotion([
            'type' => 'simple_discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);
        $promotion->shops()->sync([$targetShop->id]);

        $service = app(PromotionService::class);

        $this->assertTrue($service->isProductEligible($targetProduct, $promotion));
        $this->assertFalse($service->isProductEligible($otherProduct, $promotion));
    }

    public function test_simple_discount_applies_only_to_targeted_products(): void
    {
        [$productA, $productB, $productC, $items] = $this->buildMixedCart();

        $promotion = $this->createPromotion([
            'type' => 'simple_discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);
        $promotion->products()->sync([$productA->id, $productB->id]);

        $service = app(PromotionService::class);
        $result = $service->evaluateDiscountPromotion($promotion->id, 600, $items);

        $this->assertSame(300.0, $result['eligible_subtotal']);
        $this->assertSame(30.0, $result['discount']);
        $this->assertSame([$productA->id, $productB->id], $result['eligible_products']);
        $this->assertSame([$productC->id], $result['ineligible_products']);
        $this->assertTrue($result['applies']);
    }

    public function test_spend_x_discount_uses_eligible_subtotal_for_min_spend(): void
    {
        [$productA, $productB, $productC, $items] = $this->buildMixedCart();

        $promotion = $this->createPromotion([
            'type' => 'spend_x_discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_spend' => 250,
        ]);
        $promotion->products()->sync([$productA->id]);

        $service = app(PromotionService::class);
        $result = $service->evaluateDiscountPromotion($promotion->id, 600, $items);

        $this->assertSame(100.0, $result['eligible_subtotal']);
        $this->assertSame(0.0, $result['discount']);
        $this->assertFalse($result['applies']);
        $this->assertSame([$productA->id], $result['eligible_products']);
        $this->assertSame([$productB->id, $productC->id], $result['ineligible_products']);
    }

    public function test_spend_x_get_gift_uses_category_targeting(): void
    {
        $vendor = $this->createVendor();
        $categoryA = $this->createCategory('Category A');
        $categoryB = $this->createCategory('Category B');
        $shop = $this->createShop($vendor, 'Gift Shop');

        $eligibleProduct = $this->createProduct($categoryA, $vendor, 'Eligible Gift Product');
        $ineligibleProduct = $this->createProduct($categoryB, $vendor, 'Ineligible Gift Product');

        $this->createVariantWithShopVariant($eligibleProduct, $shop);
        $this->createVariantWithShopVariant($ineligibleProduct, $shop);

        $promotion = $this->createPromotion([
            'type' => 'spend_x_get_gift',
            'min_spend' => 250,
        ]);
        $promotion->categories()->sync([$categoryA->id]);

        $service = app(PromotionService::class);
        $result = $service->applyAutomaticOrderPromotions(
            null,
            null,
            400,
            true,
            collect([
                $this->cartItem($eligibleProduct, $shop, 1, 300),
                $this->cartItem($ineligibleProduct, $shop, 1, 100),
            ])
        );

        $this->assertCount(1, $result['gifts']);
        $this->assertSame(300.0, $result['gifts'][0]['eligible_subtotal']);
    }

    public function test_spend_x_get_points_and_free_shipping_use_targeting_and_eligible_subtotal(): void
    {
        $vendorA = $this->createVendor('Vendor A');
        $vendorB = $this->createVendor('Vendor B');
        $category = $this->createCategory();
        $shopA = $this->createShop($vendorA, 'Vendor A Shop');
        $shopB = $this->createShop($vendorB, 'Vendor B Shop');

        $pointsProduct = $this->createProduct($category, $vendorA, 'Points Product');
        $shippingProduct = $this->createProduct($category, $vendorA, 'Shipping Product');
        $otherProduct = $this->createProduct($category, $vendorB, 'Other Vendor Product');

        $this->createVariantWithShopVariant($pointsProduct, $shopA);
        $this->createVariantWithShopVariant($shippingProduct, $shopA);
        $this->createVariantWithShopVariant($otherProduct, $shopB);

        $pointsPromotion = $this->createPromotion([
            'type' => 'spend_x_get_points',
            'min_spend' => 250,
            'reward_points' => 50,
        ]);
        $pointsPromotion->vendors()->sync([$vendorA->id]);

        $shippingPromotion = $this->createPromotion([
            'type' => 'spend_x_get_free_shipping',
            'min_spend' => 250,
        ]);
        $shippingPromotion->shops()->sync([$shopA->id]);

        $items = collect([
            $this->cartItem($pointsProduct, $shopA, 1, 200),
            $this->cartItem($shippingProduct, $shopA, 1, 0),
            $this->cartItem($otherProduct, $shopB, 1, 160),
        ]);

        $service = app(PromotionService::class);

        $pointsResult = $service->applyAutomaticOrderPromotions(null, null, 360, true, $items);
        $this->assertSame(0, $pointsResult['points_expected']);

        $hasFreeShipping = $service->hasActiveAutomaticFreeShipping(360, $items);
        $deliveryPrice = $service->resolveAutomaticFreeShippingDeliveryPrice(25, 360, $items);

        $this->assertFalse($hasFreeShipping);
        $this->assertSame(25.0, $deliveryPrice);
    }

    private function createVendor(string $name = 'Vendor'): Vendor
    {
        return Vendor::create([
            'name' => ['en' => $name, 'ar' => $name],
            'owner_name' => $name . ' Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-' . str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);
    }

    private function createCategory(string $name = 'Category'): Category
    {
        return Category::create([
            'name' => ['en' => $name, 'ar' => $name],
            'icon' => null,
            'parent_id' => null,
            'is_active' => true,
            'is_restaurant' => false,
        ]);
    }

    private function createShop(Vendor $vendor, string $name): Shop
    {
        return Shop::create([
            'name' => ['en' => $name, 'ar' => $name],
            'description' => ['en' => $name, 'ar' => $name],
            'address' => ['en' => 'Test Address', 'ar' => 'Test Address'],
            'phone' => '0500000000',
            'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
            'is_default' => false,
            'is_free_delivery' => false,
            'is_service_provider' => false,
            'is_restaurant' => false,
        ]);
    }

    private function createProduct(Category $category, Vendor $vendor, string $name): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => $name, 'ar' => $name],
            'description' => ['en' => $name, 'ar' => $name],
            'price' => 100,
            'discount' => 0,
            'quantity' => 0,
            'is_instant_delivery' => false,
        ]);
    }

    private function createVariantWithShopVariant(Product $product, Shop $shop): array
    {
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => ['en' => $product->name, 'ar' => $product->name],
            'sku' => null,
            'model' => null,
            'barcode' => null,
            'attributes_values_ids' => [],
            'is_trend' => false,
            'is_active' => true,
        ]);

        $shopVariant = ShopProductVariant::create([
            'product_variant_id' => $variant->id,
            'shop_id' => $shop->id,
            'quantity' => 100,
            'price' => 100,
        ]);

        return [$variant, $shopVariant];
    }

    private function createPromotion(array $attributes = []): Promotion
    {
        return Promotion::create(array_merge([
            'name' => ['en' => 'Promotion', 'ar' => 'Promotion'],
            'description' => ['en' => 'Promotion', 'ar' => 'Promotion'],
            'type' => 'simple_discount',
            'is_active' => true,
            'position' => 'top',
            'starts_at' => null,
            'ends_at' => null,
            'min_spend' => null,
            'discount_value' => null,
            'discount_type' => null,
            'gift_description' => null,
            'reward_points' => null,
        ], $attributes));
    }

    private function cartItem(Product $product, Shop $shop, int $quantity, int $unitPrice): array
    {
        return [
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'shop_id' => $shop->id,
            'vendor_id' => $product->vendor_id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $unitPrice * $quantity,
        ];
    }

    /**
     * @return array{0: Product, 1: Product, 2: Product, 3: Collection<int, array<string, mixed>>}
     */
    private function buildMixedCart(): array
    {
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $shop = $this->createShop($vendor, 'Cart Shop');

        $productA = $this->createProduct($category, $vendor, 'Product A');
        $productB = $this->createProduct($category, $vendor, 'Product B');
        $productC = $this->createProduct($category, $vendor, 'Product C');

        $this->createVariantWithShopVariant($productA, $shop);
        $this->createVariantWithShopVariant($productB, $shop);
        $this->createVariantWithShopVariant($productC, $shop);

        return [
            $productA,
            $productB,
            $productC,
            collect([
                $this->cartItem($productA, $shop, 1, 100),
                $this->cartItem($productB, $shop, 1, 200),
                $this->cartItem($productC, $shop, 1, 300),
            ]),
        ];
    }
}
