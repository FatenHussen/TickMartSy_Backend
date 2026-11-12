<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Events\LowStockDetected;
use App\Events\OrderCreated;
use App\Models\Area;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\City;
use App\Models\Currency;
use App\Models\Governorate;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Covers acceptance criteria B-01, B-02, B-05 and B-06: the cart response must
 * carry sku / model / available_quantity / attributes, and an order must show
 * the values captured when it was placed rather than today's catalogue.
 */
class CartItemCatalogFieldsTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Currency::create([
            'code' => 'USD', 'name' => ['en' => 'US Dollar'], 'symbol' => '$',
            'exchange_rate' => 1, 'is_default' => true, 'is_active' => true,
        ]);
        $this->customer = User::factory()->create();
        $this->actingAs($this->customer, 'user');
        Event::fake([OrderCreated::class, LowStockDetected::class]);
    }

    /** B-01 */
    public function test_cart_items_expose_sku_model_available_quantity_and_attributes(): void
    {
        $offer = $this->offer(stock: 8, sku: 'TIK-SHIRT-BLU-M', model: 'MDL-2219', withAttributes: true);

        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 2,
        ])->assertOk();

        $response = $this->getJson('/api/user/cart')->assertOk();
        $item = $response->json('data.items.0');

        $this->assertSame('TIK-SHIRT-BLU-M', $item['sku']);
        $this->assertSame('MDL-2219', $item['model']);
        $this->assertSame(8, $item['available_quantity']);
        $this->assertSame($offer->productVariant->product_id, $item['product_id']);
        $this->assertCount(1, $item['attributes']);
        $this->assertSame('Blue', $item['attributes'][0]['value']);
        $this->assertArrayHasKey('id', $item['attributes'][0]);
        $this->assertArrayHasKey('type', $item['attributes'][0]);
    }

    /** B-02: a variant without its own SKU falls back to the product's, never erroring. */
    public function test_variant_without_sku_falls_back_to_product_sku_then_null(): void
    {
        $withProductSku = $this->offer(sku: null, productSku: 'PRODUCT-SKU-1');
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $withProductSku->id, 'quantity' => 1,
        ])->assertOk();

        $item = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0');
        $this->assertSame('PRODUCT-SKU-1', $item['sku']);
        $this->assertNull($item['model']);

        // Neither variant nor product has a SKU: null, and the request still succeeds.
        $bare = $this->offer(sku: null, productSku: null);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $bare->id, 'quantity' => 1,
        ])->assertOk();

        $items = collect($this->getJson('/api/user/cart')->assertOk()->json('data.items'));
        $bareItem = $items->firstWhere('shop_product_variant_id', $bare->id);
        $this->assertNull($bareItem['sku']);
    }

    /** B-05: renaming an attribute value keeps its id, so the cart stays valid. */
    public function test_renaming_an_attribute_value_keeps_its_id(): void
    {
        $offer = $this->offer(withAttributes: true);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $before = $this->getJson('/api/user/cart')->json('data.items.0.attributes.0');

        AttributeValue::query()->whereKey($before['id'])->first()
            ->forceFill(['name' => ['en' => 'Navy']])->save();

        $after = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0.attributes.0');

        $this->assertSame($before['id'], $after['id']);
        $this->assertSame('Navy', $after['value']);
    }

    /** B-06: an order shows the values stored at purchase time. */
    public function test_order_items_keep_the_snapshot_taken_at_purchase_time(): void
    {
        $offer = $this->offer(stock: 9, sku: 'SNAP-SKU', model: 'SNAP-MODEL', withAttributes: true);

        $this->postJson('/api/user/orders', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 1],
        ]))->assertSuccessful();

        $this->assertDatabaseHas('order_items', [
            'shop_product_variant_id' => $offer->id,
            'product_id' => $offer->productVariant->product_id,
            'sku' => 'SNAP-SKU',
            'model' => 'SNAP-MODEL',
            'available_quantity' => 9,
        ]);

        // The catalogue changes after the fact; the order must not follow it.
        $offer->productVariant->forceFill([
            'sku' => 'CHANGED-SKU', 'model' => 'CHANGED-MODEL', 'quantity' => 1,
        ])->save();

        $orderId = \App\Models\OrderItem::query()->value('order_id');
        $item = $this->getJson("/api/user/orders/{$orderId}")->assertOk()->json('data.items.0');

        $this->assertSame('SNAP-SKU', $item['sku']);
        $this->assertSame('SNAP-MODEL', $item['model']);
        $this->assertSame(9, $item['available_quantity']);
        $this->assertSame($offer->productVariant->product_id, $item['product_id']);
        $this->assertNotEmpty($item['attributes']);
        $this->assertNotNull($item['shop']['name']);
    }

    private function offer(
        int $stock = 10,
        ?string $sku = 'SKU-1',
        ?string $model = null,
        ?string $productSku = null,
        bool $withAttributes = false,
    ): ShopProductVariant {
        $vendor = Vendor::create([
            'name' => ['en' => 'Seller'], 'owner_name' => 'Owner', 'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(), 'contract_number' => 'CNT-'.str()->uuid(),
            'contract_duration_months' => 12, 'commission_rate' => 5, 'is_active' => true,
        ]);
        $category = Category::create(['name' => ['en' => 'Clothes'], 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'vendor_id' => $vendor->id, 'name' => ['en' => 'Shirt'],
            'price' => 25, 'quantity' => 100, 'discount' => 0, 'sku' => $productSku,
            'approval_status' => ProductApprovalStatus::APPROVED, 'is_active' => true, 'is_visible' => true,
        ]);

        $valueIds = [];
        if ($withAttributes) {
            $attribute = CategoryAttribute::create([
                'category_id' => $category->id, 'name' => ['en' => 'Color'], 'type' => 'color',
            ]);
            $value = AttributeValue::create([
                'category_attribute_id' => $attribute->id, 'name' => ['en' => 'Blue'],
            ]);
            $valueIds = [$value->id];
        }

        $variant = $product->variants()->create([
            'price' => 25, 'quantity' => $stock, 'is_active' => true,
            'sku' => $sku, 'model' => $model, 'attributes_values_ids' => $valueIds,
        ]);
        $shop = Shop::create([
            'name' => ['en' => 'Shop'], 'email' => str()->uuid().'@example.com', 'vendor_id' => $vendor->id,
            'is_active' => true, 'is_free_delivery' => true, 'min_order_amount' => 0,
        ]);

        return $variant->shopVariants()->create(['shop_id' => $shop->id, 'cost_price' => 10])
            ->load(['productVariant.product', 'shop']);
    }

    private function checkoutPayload(array $items): array
    {
        $address = $this->customer->addresses()->first();
        if (! $address) {
            $governorate = Governorate::create(['name' => ['en' => 'Damascus'], 'is_active' => true]);
            $city = City::create(['name' => ['en' => 'Damascus'], 'governorate_id' => $governorate->id]);
            $area = Area::create(['name' => ['en' => 'Center'], 'city_id' => $city->id, 'base_fee' => 0]);
            $address = $this->customer->addresses()->create([
                'area_id' => $area->id, 'street_name' => 'Test street', 'contact_phone' => '0999999999',
                'is_default' => true,
            ]);
        }
        $payment = PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true]);

        return [
            'address_id' => $address->id, 'payment_method_id' => $payment->id,
            'is_instant_delivery' => true, 'items' => $items,
        ];
    }
}
