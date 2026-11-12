<?php

namespace Tests\Feature;

use App\Enums\CartType;
use App\Enums\ProductApprovalStatus;
use App\Events\LowStockDetected;
use App\Events\OrderCreated;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Acceptance criteria S-01 .. S-10: the cart follows the account, not the
 * device, and syncing it up must never inflate quantities.
 */
class CartSyncAcrossDevicesTest extends TestCase
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

    /** S-01: a second device gets every field needed to draw the cart. */
    public function test_cart_returns_rows_renderable_without_the_product_page(): void
    {
        $offer = $this->offer(stock: 8, sku: 'TIK-SHIRT-BLU-M', model: 'MDL-2219', withAttributes: true);

        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id,
            'quantity' => 2,
            'note' => 'no bag',
            'extras' => [],
        ])->assertOk();

        // A different device: same account, no local state at all.
        $item = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0');

        $this->assertSame($offer->id, $item['shop_product_variant_id']);
        $this->assertNotNull($item['id']);
        $this->assertSame('Shirt', $item['name']);
        $this->assertNotNull($item['image']);
        $this->assertSame(2, $item['quantity']);
        $this->assertSame('no bag', $item['note']);
        $this->assertSame(8, $item['available_quantity']);
        $this->assertSame('TIK-SHIRT-BLU-M', $item['sku']);
        $this->assertSame('MDL-2219', $item['model']);
        $this->assertFalse($item['is_instant_delivery']);
        $this->assertNotNull($item['category_id']);
        $this->assertNotNull($item['shop']['name']);
        $this->assertCount(1, $item['attributes']);

        // Prices are computed by the server, in ApiFormattedMoney shape.
        foreach (['unit_price', 'line_total'] as $key) {
            $this->assertArrayHasKey('amount', $item[$key]);
            $this->assertArrayHasKey('currency', $item[$key]);
            $this->assertArrayHasKey('symbol', $item[$key]);
            $this->assertArrayHasKey('formatted', $item[$key]);
        }
        $this->assertEquals(25, $item['unit_price']['amount']);
        $this->assertEquals(50, $item['line_total']['amount']);
        $this->assertFalse($item['unavailable']);
    }

    /** S-02 / S-03: the returned id drives update and delete from any device. */
    public function test_returned_row_id_drives_update_and_delete(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $id = $this->getJson('/api/user/cart')->json('data.items.0.id');

        $this->putJson("/api/user/cart/items/{$id}", ['quantity' => 4])->assertOk();
        $this->assertSame(4, $this->getJson('/api/user/cart')->json('data.items.0.quantity'));

        $this->deleteJson("/api/user/cart/items/{$id}")->assertOk();
        $this->assertSame([], $this->getJson('/api/user/cart')->json('data.items'));
    }

    /** S-04: repeated sign-out/sign-in syncing must never inflate quantities. */
    public function test_repeated_merge_never_inflates_quantity(): void
    {
        $offer = $this->offer(stock: 10);
        $payload = ['items' => [['shop_product_variant_id' => $offer->id, 'quantity' => 1]]];

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/user/cart/merge', $payload)->assertOk();
        }

        $items = $this->getJson('/api/user/cart')->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame(1, $items[0]['quantity'], 'merge must be idempotent');
    }

    /** S-05: merging takes max(existing, incoming), never the sum. */
    public function test_merge_takes_the_maximum_quantity(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 3,
        ])->assertOk();

        // Guest cart held a smaller quantity: the server's larger one wins.
        $this->postJson('/api/user/cart/merge', [
            'items' => [['shop_product_variant_id' => $offer->id, 'quantity' => 2]],
        ])->assertOk();
        $this->assertSame(3, $this->getJson('/api/user/cart')->json('data.items.0.quantity'));

        // Guest cart held more: that one wins. Still max, not 3 + 5.
        $this->postJson('/api/user/cart/merge', [
            'items' => [['shop_product_variant_id' => $offer->id, 'quantity' => 5]],
        ])->assertOk();
        $this->assertSame(5, $this->getJson('/api/user/cart')->json('data.items.0.quantity'));
    }

    /** Option B: mode=set pins the quantity instead of summing. */
    public function test_set_mode_pins_quantity_while_add_mode_sums(): void
    {
        $offer = $this->offer(stock: 10);

        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 2,
        ])->assertOk();
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 2,
        ])->assertOk();
        $this->assertSame(4, $this->getJson('/api/user/cart')->json('data.items.0.quantity'));

        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 2, 'mode' => 'set',
        ])->assertOk();
        $this->assertSame(2, $this->getJson('/api/user/cart')->json('data.items.0.quantity'));
    }

    /** S-06: notes and extras come back exactly as sent. */
    public function test_note_and_extras_round_trip(): void
    {
        $offer = $this->offer(stock: 10);
        $extras = [['id' => $this->extraDetailId($offer), 'quantity' => 2]];

        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id,
            'quantity' => 1,
            'note' => 'بدون كيس',
            'extras' => $extras,
        ])->assertOk();

        $item = $this->getJson('/api/user/cart')->json('data.items.0');
        $this->assertSame('بدون كيس', $item['note']);
        $this->assertSame($extras, $item['extras']);
    }

    /** S-07: the cart type and its linked id survive for the second device. */
    public function test_cart_type_and_linked_id_are_returned(): void
    {
        $offer = $this->offer(stock: 10);

        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id,
            'quantity' => 1,
            'cart_type' => CartType::ADMIN_CART->value,
        ])->assertOk();

        $data = $this->getJson('/api/user/cart')->assertOk()->json('data');
        $this->assertSame(CartType::ADMIN_CART->value, $data['cart_type']);
        $this->assertArrayHasKey('recipe_id', $data);
        $this->assertArrayHasKey('basket_schedule_id', $data);
    }

    /** A default cart still reports its type. */
    public function test_default_cart_type_is_reported(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $this->assertSame(
            CartType::DEFAULT->value,
            $this->getJson('/api/user/cart')->json('data.cart_type')
        );
    }

    /**
     * S-08: a variant deleted from the dashboard never arrives as an orderable
     * row. The spec allows either dropping it or flagging it; an existing
     * `deleting` hook on ShopProductVariant prunes cart rows, so it is dropped.
     */
    public function test_deleted_variant_does_not_arrive_as_an_orderable_row(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $offer->productVariant->delete();

        $items = $this->getJson('/api/user/cart')->assertOk()->json('data.items');
        $this->assertSame([], $items);
    }

    /**
     * A variant merely deactivated (not deleted) stays in the cart and must be
     * flagged, so the user is told why instead of being refused at checkout.
     */
    public function test_deactivated_variant_is_flagged_unavailable(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $offer->productVariant->forceFill(['is_active' => false])->save();

        $item = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0');
        $this->assertTrue($item['unavailable']);
        $this->assertNotNull($item['unavailable_reason']);
    }

    /** Out-of-stock rows are flagged rather than silently dropped. */
    public function test_out_of_stock_row_is_flagged_unavailable(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $offer->productVariant->forceFill(['quantity' => 0])->save();

        $item = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0');
        $this->assertTrue($item['unavailable']);
        $this->assertSame(0, $item['available_quantity']);
    }

    /** Section 6.4: a disabled shop makes the row unavailable too. */
    public function test_disabled_shop_is_flagged_unavailable(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $offer->shop->forceFill(['is_active' => false])->save();

        $this->assertTrue($this->getJson('/api/user/cart')->json('data.items.0.unavailable'));
    }

    /** Section 6.2: a quantity above stock is clamped down to what is left. */
    public function test_quantity_is_clamped_when_stock_drops(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 6,
        ])->assertOk();

        $offer->productVariant->forceFill(['quantity' => 2])->save();

        $item = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0');
        $this->assertSame(2, $item['quantity']);
        $this->assertSame(2, $item['available_quantity']);
        $this->assertDatabaseHas('cart_items', ['id' => $item['id'], 'quantity' => 2]);
    }

    /** Section 6.3: the cart is not a price lock. */
    public function test_price_changes_are_reflected(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 2,
        ])->assertOk();

        $offer->productVariant->forceFill(['price' => 30])->save();

        $item = $this->getJson('/api/user/cart')->assertOk()->json('data.items.0');
        $this->assertEquals(30, $item['unit_price']['amount']);
        $this->assertEquals(60, $item['line_total']['amount']);
    }

    /** S-09: instant-delivery products are marked so mixing stays blocked. */
    public function test_instant_delivery_flag_is_returned(): void
    {
        $offer = $this->offer(stock: 10, instant: true);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $this->assertTrue($this->getJson('/api/user/cart')->json('data.items.0.is_instant_delivery'));
    }

    /** S-10: clearing the cart on the server empties it everywhere. */
    public function test_clearing_the_cart_empties_it_server_side(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $this->deleteJson('/api/user/cart')->assertOk();

        $this->assertSame([], $this->getJson('/api/user/cart')->json('data.items'));
        $this->assertDatabaseCount('cart_items', 0);
    }

    /** Merge still refuses more than the stock allows. */
    public function test_merge_rejects_quantity_above_stock(): void
    {
        $offer = $this->offer(stock: 2);

        $this->postJson('/api/user/cart/merge', [
            'items' => [['shop_product_variant_id' => $offer->id, 'quantity' => 5]],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('cart_items', 0);
    }

    /** One user's cart must never leak into another's. */
    public function test_cart_is_scoped_to_the_account(): void
    {
        $offer = $this->offer(stock: 10);
        $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 1,
        ])->assertOk();

        $this->actingAs(User::factory()->create(), 'user');
        $this->assertSame([], $this->getJson('/api/user/cart')->json('data.items'));
    }

    private function extraDetailId(ShopProductVariant $offer): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('product_extra_details')->insertGetId([
            'category_id' => $offer->productVariant->product->category_id,
            'detail_key' => 'Size',
            'detail_value' => 'Large',
            'price' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function offer(
        int $stock = 10,
        ?string $sku = 'SKU-1',
        ?string $model = null,
        bool $withAttributes = false,
        bool $instant = false,
    ): ShopProductVariant {
        $vendor = Vendor::create([
            'name' => ['en' => 'Seller'], 'owner_name' => 'Owner', 'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(), 'contract_number' => 'CNT-'.str()->uuid(),
            'contract_duration_months' => 12, 'commission_rate' => 5, 'is_active' => true,
        ]);
        $category = Category::create(['name' => ['en' => 'Clothes'], 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'vendor_id' => $vendor->id, 'name' => ['en' => 'Shirt'],
            'price' => 25, 'quantity' => 100, 'discount' => 0,
            'thumbnail' => 'https://example.test/shirt.jpg',
            'is_instant_delivery' => $instant,
            'approval_status' => ProductApprovalStatus::APPROVED, 'is_active' => true, 'is_visible' => true,
        ]);

        $valueIds = [];
        if ($withAttributes) {
            $attribute = CategoryAttribute::create([
                'category_id' => $category->id, 'name' => ['en' => 'Color'], 'type' => 'color',
            ]);
            $valueIds = [AttributeValue::create([
                'category_attribute_id' => $attribute->id, 'name' => ['en' => 'Blue'],
            ])->id];
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
}
