<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Events\LowStockDetected;
use App\Events\OrderCreated;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Currency;
use App\Models\Governorate;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\Vendor;
use App\Services\User\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PurchaseVariantValidationTest extends TestCase
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

    public function test_cart_requires_exact_identity_and_positive_integer_quantity_without_mutation(): void
    {
        $offer = $this->offer();
        foreach ([
            ['product_id' => $offer->productVariant->product_id, 'quantity' => 1],
            ['shop_product_variant_id' => 999999, 'quantity' => 1],
            ['shop_product_variant_id' => $offer->id],
            ['shop_product_variant_id' => $offer->id, 'quantity' => null],
            ['shop_product_variant_id' => $offer->id, 'quantity' => 0],
            ['shop_product_variant_id' => $offer->id, 'quantity' => -1],
            ['shop_product_variant_id' => $offer->id, 'quantity' => 1.5],
        ] as $payload) {
            $this->postJson('/api/user/cart/items', $payload)
                ->assertUnprocessable()->assertJsonPath('status', false)->assertJsonStructure(['errors']);
            $this->assertDatabaseCount('cart_items', 0);
        }
    }

    public function test_cart_keeps_the_selected_offer_and_enforces_resulting_line_stock_and_limit(): void
    {
        $other = $this->offer();
        $selected = $this->offer(stock: 5, limit: 3);
        $payload = ['shop_product_variant_id' => $selected->id, 'quantity' => 2, 'price' => 0];
        $this->postJson('/api/user/cart/items', $payload)->assertOk()
            ->assertJsonPath('data.shop_product_variant_id', $selected->id);
        $this->postJson('/api/user/cart/items', $payload)->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
        $this->assertDatabaseHas('cart_items', ['shop_product_variant_id' => $selected->id, 'quantity' => 2]);
        $this->assertDatabaseMissing('cart_items', ['shop_product_variant_id' => $other->id]);

        $selected->productVariant->product->update(['max_purchase_quantity' => null]);
        $selected->productVariant->update(['quantity' => 2]);
        $this->postJson('/api/user/cart/items', ['shop_product_variant_id' => $selected->id, 'quantity' => 1])
            ->assertUnprocessable();
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_cart_rejects_unsellable_offers_instead_of_using_product_stock(): void
    {
        $cases = [
            fn ($offer) => $offer->productVariant->update(['quantity' => 0]),
            fn ($offer) => $offer->productVariant->update(['is_active' => false]),
            fn ($offer) => $offer->productVariant->product->update(['is_active' => false]),
            fn ($offer) => $offer->productVariant->product->update(['is_visible' => false]),
            fn ($offer) => $offer->productVariant->product->update(['approval_status' => ProductApprovalStatus::PENDING]),
            fn ($offer) => $offer->shop->update(['is_active' => false]),
            fn ($offer) => $offer->productVariant->product->category->update(['is_active' => false]),
            fn ($offer) => $offer->productVariant->delete(),
            fn ($offer) => $offer->delete(),
        ];
        foreach ($cases as $makeUnavailable) {
            $offer = $this->offer();
            $makeUnavailable($offer);
            $this->postJson('/api/user/cart/items', ['shop_product_variant_id' => $offer->id, 'quantity' => 1])
                ->assertUnprocessable();
            $this->assertDatabaseCount('cart_items', 0);
        }
    }

    public function test_cart_service_rejects_missing_identity_even_when_called_without_request_validation(): void
    {
        $this->offer();
        try {
            app(CartService::class)->add($this->customer->id, ['quantity' => 1]);
            $this->fail('A product must never select an implicit variant.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('shop_product_variant_id', $exception->errors());
        }
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_update_rechecks_stock_and_saves_nothing_on_failure(): void
    {
        $offer = $this->offer();
        $response = $this->postJson('/api/user/cart/items', [
            'shop_product_variant_id' => $offer->id, 'quantity' => 2, 'note' => 'original',
        ])->assertOk();
        $offer->productVariant->update(['quantity' => 1]);
        $this->putJson('/api/user/cart/items/'.$response->json('data.id'), ['quantity' => 2, 'note' => 'changed'])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseHas('cart_items', ['quantity' => 2, 'note' => 'original']);
    }

    public function test_checkout_revalidates_after_cart_add_and_rolls_back_failed_order(): void
    {
        $offer = $this->offer();
        $payload = $this->checkoutPayload([['shop_product_variant_id' => $offer->id, 'quantity' => 2]]);
        $this->postJson('/api/user/cart/items', $payload['items'][0])->assertOk();
        $offer->productVariant->update(['quantity' => 1]);

        foreach (['/api/user/orders/preview', '/api/user/orders'] as $path) {
            $this->postJson($path, $payload)->assertUnprocessable()
                ->assertJsonValidationErrors('items.0.quantity');
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(1, $offer->productVariant->fresh()->quantity);
    }

    public function test_checkout_combines_stock_across_duplicate_rows_and_shop_offers(): void
    {
        $offer = $this->offer(stock: 5);
        $secondOffer = $offer->productVariant->shopVariants()->create([
            'shop_id' => $offer->shop_id, 'cost_price' => 9,
        ]);
        foreach ([$offer->id, $secondOffer->id] as $secondId) {
            $payload = $this->checkoutPayload([
                ['shop_product_variant_id' => $offer->id, 'quantity' => 3],
                ['shop_product_variant_id' => $secondId, 'quantity' => 3],
            ]);
            foreach (['/api/user/orders/preview', '/api/user/orders'] as $path) {
                $this->postJson($path, $payload)->assertUnprocessable()
                    ->assertJsonValidationErrors('items.1.quantity');
            }
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $offer->productVariant->fresh()->quantity);
    }

    public function test_checkout_enforces_product_limit_across_distinct_variants(): void
    {
        $offer = $this->offer(stock: 10, limit: 3);
        $variant = $offer->productVariant->product->variants()->create([
            'price' => 30, 'quantity' => 10, 'is_active' => true, 'attributes_values_ids' => [],
        ]);
        $second = $variant->shopVariants()->create(['shop_id' => $offer->shop_id, 'cost_price' => 10]);
        $payload = $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 2],
            ['shop_product_variant_id' => $second->id, 'quantity' => 2],
        ]);
        $this->postJson('/api/user/orders/preview', $payload)->assertUnprocessable()
            ->assertJsonValidationErrors('items.1.quantity');
    }

    public function test_checkout_uses_selected_identity_and_server_prices_then_decrements_exact_inventory(): void
    {
        $unselected = $this->offer(stock: 9);
        $selected = $this->offer(stock: 7);
        $payload = $this->checkoutPayload([[
            'shop_product_variant_id' => $selected->id, 'quantity' => 2,
            'price' => 0.01, 'price_after_discount' => 0.01, 'total' => 0.02,
        ]]);
        $this->postJson('/api/user/orders/preview', $payload)->assertOk();
        $this->postJson('/api/user/orders', $payload)->assertOk();
        $this->assertDatabaseHas('order_items', [
            'shop_product_variant_id' => $selected->id, 'quantity' => 2, 'unit_price' => 25, 'total' => 50,
        ]);
        $this->assertSame(5, $selected->productVariant->fresh()->quantity);
        $this->assertSame(9, $unselected->productVariant->fresh()->quantity);
    }

    public function test_reorder_recalculates_current_price_and_inventory_for_scheduled_orders(): void
    {
        $offer = $this->offer(stock: 10);
        $payload = $this->checkoutPayload([['shop_product_variant_id' => $offer->id, 'quantity' => 2]]);
        $payload['is_instant_delivery'] = false;
        $payload['scheduled_delivery_at'] = now()->addDays(4)->format('Y-m-d H:i');
        $this->postJson('/api/user/orders', $payload)->assertOk();
        $original = Order::firstOrFail();
        $offer->productVariant->update(['price' => 40]);
        $this->postJson('/api/user/orders/reorder/'.$original->id)->assertOk();
        $this->assertDatabaseHas('order_items', [
            'order_id' => Order::max('id'), 'unit_price' => 40, 'quantity' => 2, 'total' => 80,
        ]);
        $this->assertSame(6, $offer->productVariant->fresh()->quantity);
        $offer->productVariant->update(['quantity' => 0]);
        $this->postJson('/api/user/orders/reorder/'.$original->id)->assertUnprocessable();
        $this->assertDatabaseCount('orders', 2);
    }

    private function offer(int $stock = 10, ?int $limit = null): ShopProductVariant
    {
        $vendor = Vendor::create([
            'name' => ['en' => 'Seller'], 'owner_name' => 'Owner', 'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(), 'contract_number' => 'CNT-'.str()->uuid(),
            'contract_duration_months' => 12, 'commission_rate' => 5, 'is_active' => true,
        ]);
        $category = Category::create(['name' => ['en' => 'Clothes'], 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'vendor_id' => $vendor->id, 'name' => ['en' => 'Shirt'],
            'price' => 25, 'quantity' => 100, 'discount' => 0, 'max_purchase_quantity' => $limit,
            'approval_status' => ProductApprovalStatus::APPROVED, 'is_active' => true, 'is_visible' => true,
        ]);
        $variant = $product->variants()->create([
            'price' => 25, 'quantity' => $stock, 'is_active' => true, 'attributes_values_ids' => [],
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
