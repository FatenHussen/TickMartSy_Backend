<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\UserBasketSchedule;
use App\Models\Vendor;
use App\Services\ScheduledBasketAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasketVariantValidationTest extends TestCase
{
    use RefreshDatabase;

    private Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(), 'user');
        $this->schedule = Schedule::create([
            'name' => ['en' => 'Weekly'],
            'interval_days' => 7,
            'is_active' => true,
        ]);
    }

    public function test_custom_basket_rejects_missing_identity_and_fractional_quantity_without_creating_a_draft(): void
    {
        $offer = $this->createOffer();

        $this->postJson($this->customUrl().'/items', ['product_id' => $offer->productVariant->product_id, 'quantity' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('shop_product_variant_id');
        $this->postJson($this->customUrl().'/items', ['shop_product_variant_id' => $offer->id, 'quantity' => 1.5])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');

        $this->assertDatabaseCount('user_basket_schedules', 0);
        $this->assertDatabaseCount('user_basket_schedule_items', 0);
    }

    public function test_custom_basket_preserves_the_exact_offer_and_replaces_its_quantity(): void
    {
        $this->createOffer();
        $selected = $this->createOffer();

        foreach ([2, 3] as $quantity) {
            $this->postJson($this->customUrl().'/items', [
                'shop_product_variant_id' => $selected->id,
                'quantity' => $quantity,
                'price' => 0.01,
            ])->assertOk()
                ->assertJsonPath('data.items.0.shop_product_variant_id', $selected->id)
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.summary.original_price', $quantity * 20);
        }

        $this->assertDatabaseCount('user_basket_schedule_items', 1);
        $this->assertDatabaseHas('user_basket_schedule_items', [
            'product_id' => $selected->productVariant->product_id,
            'shop_product_variant_id' => $selected->id,
            'quantity' => 3,
        ]);
    }

    public function test_custom_basket_blocks_unavailable_offer_without_substituting_an_available_one(): void
    {
        $available = $this->createOffer();
        $unavailableVariant = $available->productVariant->product->variants()->create([
            'price' => 10,
            'quantity' => 0,
            'is_active' => true,
            'attributes_values_ids' => [],
        ]);
        $unavailable = $unavailableVariant->shopVariants()->create(['shop_id' => $available->shop_id]);

        $this->postJson($this->customUrl().'/items', [
            'shop_product_variant_id' => $unavailable->id,
            'quantity' => 1,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('user_basket_schedule_items', 0);
        $this->assertDatabaseCount('user_basket_schedules', 0);
    }

    public function test_custom_basket_update_rechecks_stock_and_purchase_limit_without_mutating_the_line(): void
    {
        $offer = $this->createOffer(['max_purchase_quantity' => 3]);
        $this->postJson($this->customUrl().'/items', ['shop_product_variant_id' => $offer->id, 'quantity' => 2])
            ->assertOk();
        $item = UserBasketSchedule::firstOrFail()->items()->firstOrFail();

        $this->putJson($this->customUrl().'/items/'.$item->id, ['quantity' => 4])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $offer->productVariant->update(['quantity' => 1]);
        $this->putJson($this->customUrl().'/items/'.$item->id, ['quantity' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');

        $this->assertSame(2, (int) $item->fresh()->quantity);
    }

    public function test_confirm_revalidates_stock_before_deleting_or_scheduling_the_draft(): void
    {
        $offer = $this->createOffer();
        $this->postJson($this->customUrl().'/items', ['shop_product_variant_id' => $offer->id, 'quantity' => 2])
            ->assertOk();
        $draft = UserBasketSchedule::firstOrFail();
        $offer->productVariant->update(['quantity' => 0]);

        foreach ([false, true] as $scheduled) {
            $this->postJson($this->customUrl().'/confirm', [
                'confirm_schedule' => $scheduled,
                'start_date' => now()->addDay()->toDateString(),
            ])->assertUnprocessable();
        }

        $this->assertTrue($draft->fresh()->is_draft);
        $this->assertSame(1, $draft->items()->count());
    }

    public function test_scheduled_basket_requires_an_exact_offer_even_for_a_simple_product(): void
    {
        $offer = $this->createOffer();

        $this->postJson('/api/user/scheduled-baskets', $this->scheduledPayload([
            ['product_id' => $offer->productVariant->product_id, 'quantity' => 1],
        ]))->assertUnprocessable()->assertJsonValidationErrors('items.0.shop_product_variant_id');

        $this->assertDatabaseCount('user_basket_schedules', 0);
    }

    public function test_scheduled_basket_uses_the_selected_offer_and_rejects_a_mismatched_product(): void
    {
        $other = $this->createOffer();
        $selected = $this->createOffer();
        $line = ['shop_product_variant_id' => $selected->id, 'quantity' => 2];

        $this->postJson('/api/user/scheduled-baskets', $this->scheduledPayload([
            [...$line, 'product_id' => $other->productVariant->product_id],
        ]))->assertUnprocessable()->assertJsonValidationErrors('items.0.product_id');
        $this->assertDatabaseCount('user_basket_schedules', 0);

        $this->postJson('/api/user/scheduled-baskets', $this->scheduledPayload([$line]))
            ->assertOk()->assertJsonPath('data.items.0.shop_product_variant_id', $selected->id);

        $this->assertDatabaseHas('user_basket_schedule_items', [
            'product_id' => $selected->productVariant->product_id,
            ...$line,
        ]);
    }

    public function test_scheduled_basket_rejects_combined_demand_beyond_shared_stock(): void
    {
        $first = $this->createOffer();
        $otherShop = Shop::create([
            'name' => ['en' => 'Other shop'],
            'email' => 'other-'.str()->uuid().'@example.com',
            'vendor_id' => $first->shop->vendor_id,
            'is_active' => true,
        ]);
        $second = $first->productVariant->shopVariants()->create(['shop_id' => $otherShop->id]);

        $this->postJson('/api/user/scheduled-baskets', $this->scheduledPayload([
            ['shop_product_variant_id' => $first->id, 'quantity' => 3],
            ['shop_product_variant_id' => $second->id, 'quantity' => 3],
        ]))->assertUnprocessable()->assertJsonValidationErrors('items.1.quantity');

        $this->assertDatabaseCount('user_basket_schedules', 0);
        $this->assertDatabaseCount('user_basket_schedule_items', 0);
    }

    public function test_scheduled_basket_update_is_atomic_and_metadata_updates_keep_existing_items(): void
    {
        $offer = $this->createOffer();
        $this->postJson('/api/user/scheduled-baskets', $this->scheduledPayload([
            ['shop_product_variant_id' => $offer->id, 'quantity' => 2],
        ]))->assertOk();
        $basket = UserBasketSchedule::firstOrFail();
        $item = $basket->items()->firstOrFail();

        $this->putJson('/api/user/scheduled-baskets/'.$basket->id, [
            'name' => 'Invalid replacement',
            'items' => [
                ['id' => $item->id, 'shop_product_variant_id' => $offer->id, 'quantity' => 6],
            ],
        ])->assertUnprocessable();
        $this->assertSame('Weekly groceries', $basket->fresh()->name);
        $this->assertSame(2, (int) $item->fresh()->quantity);

        $this->putJson('/api/user/scheduled-baskets/'.$basket->id, ['name' => 'Renamed'])
            ->assertOk()->assertJsonCount(1, 'data.items');
        $this->assertSame(2, (int) $item->fresh()->quantity);

        $this->putJson('/api/user/scheduled-baskets/'.$basket->id, [
            'items' => [
                ['id' => $item->id, 'shop_product_variant_id' => $offer->id, 'quantity' => 3],
            ],
        ])->assertOk()->assertJsonPath('data.items.0.quantity', 3);
        $this->assertSame($item->id, $basket->items()->firstOrFail()->id);
    }

    public function test_inactive_offer_is_rejected_and_reported_unavailable_in_saved_baskets(): void
    {
        $offer = $this->createOffer();
        $basket = UserBasketSchedule::create([
            'user_id' => auth('user')->id(),
            'schedule_id' => $this->schedule->id,
            'name' => 'Saved',
            'is_draft' => false,
        ]);
        $basket->items()->create([
            'product_id' => $offer->productVariant->product_id,
            'shop_product_variant_id' => $offer->id,
            'quantity' => 1,
        ]);
        $offer->shop->update(['is_active' => false]);

        $this->postJson($this->customUrl().'/items', ['shop_product_variant_id' => $offer->id, 'quantity' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('shop_product_variant_id');
        $summary = app(ScheduledBasketAvailabilityService::class)->evaluateUserSchedule($basket);
        $this->assertTrue($summary['has_issue']);
        $this->assertFalse($summary['items'][0]['is_available']);
        $this->assertSame('unavailable', $summary['items'][0]['status']);
    }

    public function test_null_stock_in_a_saved_basket_is_unavailable(): void
    {
        $offer = $this->createOffer();
        $basket = UserBasketSchedule::create([
            'user_id' => auth('user')->id(),
            'schedule_id' => $this->schedule->id,
            'is_draft' => false,
        ]);
        $basket->setRelation('schedule', $this->schedule);
        $item = $basket->items()->make([
            'product_id' => $offer->productVariant->product_id,
            'shop_product_variant_id' => $offer->id,
            'quantity' => 1,
        ]);
        $offer->productVariant->quantity = null;
        $item->setRelation('variant', $offer);
        $item->setRelation('product', $offer->productVariant->product);
        $basket->setRelation('items', $item->newCollection([$item]));

        $summary = app(ScheduledBasketAvailabilityService::class)->evaluateUserSchedule($basket);

        $this->assertTrue($summary['has_issue']);
        $this->assertSame('out_of_stock', $summary['items'][0]['status']);
        $this->assertFalse($summary['items'][0]['is_available']);
    }

    private function customUrl(): string
    {
        return '/api/user/schedules/'.$this->schedule->id.'/custom-basket';
    }

    private function scheduledPayload(array $items): array
    {
        return [
            'name' => 'Weekly groceries',
            'schedule_id' => $this->schedule->id,
            'start_date' => now()->addDay()->toDateString(),
            'items' => $items,
        ];
    }

    private function createOffer(array $productOverrides = []): ShopProductVariant
    {
        $vendor = Vendor::create([
            'name' => ['en' => 'Vendor'],
            'owner_name' => 'Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-'.str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);
        $category = Category::create(['name' => ['en' => 'Groceries'], 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => 'Product'],
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
            'is_visible' => true,
            ...$productOverrides,
        ]);
        $variant = $product->variants()->create([
            'price' => 20,
            'quantity' => 5,
            'attributes_values_ids' => [],
            'is_active' => true,
        ]);
        $shop = Shop::create([
            'name' => ['en' => 'Shop'],
            'email' => 'shop-'.str()->uuid().'@example.com',
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        return $variant->shopVariants()->create(['shop_id' => $shop->id])->load(['shop', 'productVariant.product']);
    }
}
