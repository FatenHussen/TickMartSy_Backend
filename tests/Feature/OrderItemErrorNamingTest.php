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
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Section 3.1: a rejected line must name the product. "items.0" means nothing
 * to a customer staring at a failed checkout.
 */
class OrderItemErrorNamingTest extends TestCase
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

    /** A line missing its variant id reports its position, not a bare "items.0". */
    public function test_missing_variant_id_error_names_the_position(): void
    {
        $offer = $this->offer();

        $response = $this->postJson('/api/user/orders/preview', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 1],
            ['quantity' => 1], // the broken row the frontend was sending
        ]))->assertUnprocessable();

        // The error key stays machine-readable; only the message is humanised.
        $message = $response->json('errors')['items.1.shop_product_variant_id'][0] ?? null;
        $this->assertNotNull($message);
        $this->assertStringNotContainsString('items.1', $message);
        $this->assertStringContainsString('2', $message, 'should point at the second row');
    }

    /** A line that fails on quantity names the product it belongs to. */
    public function test_quantity_error_names_the_product(): void
    {
        $offer = $this->offer(stock: 2);

        $response = $this->postJson('/api/user/orders/preview', $this->checkoutPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 0],
        ]))->assertUnprocessable();

        $message = $response->json('errors')['items.0.quantity'][0] ?? null;
        $this->assertNotNull($message);
        $this->assertStringContainsString('Shirt', $message);
        $this->assertStringNotContainsString('items.0', $message);
    }

    /** An unknown variant id still names the row rather than the index. */
    public function test_unknown_variant_id_error_is_readable(): void
    {
        $response = $this->postJson('/api/user/orders/preview', $this->checkoutPayload([
            ['shop_product_variant_id' => 999999, 'quantity' => 1],
        ]))->assertUnprocessable();

        $message = $response->json('errors')['items.0.shop_product_variant_id'][0] ?? null;
        $this->assertNotNull($message);
        $this->assertStringNotContainsString('items.0', $message);
    }

    private function offer(int $stock = 10): ShopProductVariant
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
            'is_active' => true, 'is_free_delivery' => true, 'min_order_amount' => 0,
        ]);

        return $variant->shopVariants()->create(['shop_id' => $shop->id, 'cost_price' => 10])
            ->load(['productVariant.product', 'shop']);
    }

    private function checkoutPayload(array $items): array
    {
        $governorate = Governorate::create(['name' => ['en' => 'Damascus'], 'is_active' => true]);
        $city = City::create(['name' => ['en' => 'Damascus'], 'governorate_id' => $governorate->id]);
        $area = Area::create(['name' => ['en' => 'Center'], 'city_id' => $city->id, 'base_fee' => 0]);
        $address = $this->customer->addresses()->create([
            'area_id' => $area->id, 'street_name' => 'Test street', 'contact_phone' => '0999999999',
            'is_default' => true,
        ]);
        $payment = PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Cash', 'is_active' => true]);

        return [
            'address_id' => $address->id, 'payment_method_id' => $payment->id,
            'is_instant_delivery' => true, 'items' => $items,
        ];
    }
}
