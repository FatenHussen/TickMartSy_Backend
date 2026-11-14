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
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * B-01 / B-02: tax is returned as a money amount, already inside the total,
 * and disappears when no rate is configured.
 */
class OrderTaxTest extends TestCase
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

    private function setTaxRate(float $rate): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'tikmart_tax_rate'],
            ['value' => (string) $rate, 'type' => 'number', 'group' => 'orders', 'is_active' => true],
        );
        Cache::forget('system_setting_tikmart_tax_rate');
    }

    /** B-01: tax arrives as an amount and is counted inside the total. */
    public function test_preview_returns_tax_as_an_amount_included_in_the_total(): void
    {
        $this->setTaxRate(10);
        $offer = $this->offer(stock: 10);

        // 4 x 25 = 100 goods, 10% tax = 10.
        $data = $this->postJson('/api/user/orders/preview', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 4],
        ]))->assertOk()->json();

        $this->assertEquals(10, $data['tax'], 'tax must be money, not a percentage');
        $this->assertEquals(10, $data['tax_rate']);
        $this->assertNotNull($data['tax_formatted']);

        // total must already contain the tax: 100 goods + 10 tax + delivery.
        $total = (float) str_replace([',', '$', ' '], '', (string) $data['total']);
        $delivery = (float) ($data['discounts']['delivery_price'] ?? 0);
        $this->assertEqualsWithDelta(100 + 10 + $delivery, $total, 0.01);
    }

    /** B-02: with no rate configured the line is zero, so the UI hides it. */
    public function test_preview_returns_zero_tax_when_no_rate_is_set(): void
    {
        $this->setTaxRate(0);
        $offer = $this->offer(stock: 10);

        $data = $this->postJson('/api/user/orders/preview', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 2],
        ]))->assertOk()->json();

        $this->assertEquals(0, $data['tax']);
    }

    /** The created order stores the tax it was charged. */
    public function test_created_order_snapshots_tax_and_rate(): void
    {
        $this->setTaxRate(10);
        $offer = $this->offer(stock: 10);

        $this->postJson('/api/user/orders', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 4],
        ]))->assertSuccessful();

        $this->assertDatabaseHas('orders', ['tax' => 10.0, 'tax_rate' => 10.0]);

        // A later rate change must not rewrite a past order.
        $this->setTaxRate(25);
        $orderId = \App\Models\Order::query()->value('id');
        $data = $this->getJson("/api/user/orders/{$orderId}")->assertOk()->json('data');

        $this->assertEquals(10, $data['tax']);
        $this->assertEquals(10, $data['tax_rate']);
    }

    /** Tax is charged on goods after discounts, and never on delivery. */
    public function test_tax_is_charged_on_goods_after_discounts_only(): void
    {
        $this->setTaxRate(10);
        $offer = $this->offer(stock: 10, deliveryFree: false);

        $data = $this->postJson('/api/user/orders/preview', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 4],
        ]))->assertOk()->json();

        // Goods are 100; tax stays 10 regardless of any delivery fee.
        $this->assertEquals(10, $data['tax']);
    }

    private function offer(int $stock = 10, bool $deliveryFree = true): ShopProductVariant
    {
        $vendor = Vendor::create([
            'name' => ['en' => 'Seller'], 'owner_name' => 'Owner', 'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(), 'contract_number' => 'CNT-'.str()->uuid(),
            'contract_duration_months' => 12, 'commission_rate' => 5, 'is_active' => true,
        ]);
        $category = Category::create(['name' => ['en' => 'Clothes'], 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'vendor_id' => $vendor->id, 'name' => ['en' => 'Shirt'],
            'price' => 25, 'quantity' => 100, 'discount' => 0,
            'approval_status' => ProductApprovalStatus::APPROVED, 'is_active' => true, 'is_visible' => true,
        ]);
        $variant = $product->variants()->create([
            'price' => 25, 'quantity' => $stock, 'is_active' => true, 'attributes_values_ids' => [],
        ]);
        $shop = Shop::create([
            'name' => ['en' => 'Shop'], 'email' => str()->uuid().'@example.com', 'vendor_id' => $vendor->id,
            'is_active' => true, 'is_free_delivery' => $deliveryFree, 'min_order_amount' => 0,
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
