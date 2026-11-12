<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\NotFoundException;
use App\Http\Resources\User\Cart\CartItemResource;
use App\Models\CartItem;
use App\Models\User;
use App\Traits\HasCurrencyConversion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    use HasCurrencyConversion;

    public function __construct(
        private CheckoutPolicyService $checkoutPolicy,
        private PurchasableVariantService $purchasableVariants,
    )
    {
    }

    public function list(int $userId): AnonymousResourceCollection
    {
        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with($this->itemRelations())
            ->orderByDesc('id')
            ->get();

        $this->reconcileQuantities($items);

        return CartItemResource::collection($items);
    }

    /**
     * The cart outlives the session, so stock can fall under what was saved.
     * Clamp the stored quantity down to what is left rather than letting the
     * user reach checkout and be refused there.
     */
    private function reconcileQuantities(Collection $items): void
    {
        foreach ($items as $item) {
            $stock = $item->shopProductVariant?->productVariant?->quantity;
            if ($stock === null) {
                continue;
            }

            $stock = (int) $stock;
            if ($stock > 0 && (int) $item->quantity > $stock) {
                $item->quantity = $stock;
                $item->save();
            }
        }
    }

    /** The cart type and the id it is tied to, taken from the stored rows. */
    public function cartContext(int $userId): array
    {
        $item = CartItem::query()->where('user_id', $userId)->latest('id')->first();

        return [
            'cart_type' => $item?->cart_type ?? CartType::DEFAULT->value,
            'recipe_id' => $item?->recipe_id,
            'admin_basket_id' => $item?->admin_basket_id,
            'admin_schedule_basket_id' => $item?->admin_schedule_basket_id,
            'basket_schedule_id' => $item?->basket_schedule_id,
        ];
    }

    /**
     * Guest cart -> signed-in cart, exactly once at the transition.
     * Quantities take max(existing, incoming) instead of summing, so running
     * it again cannot inflate the cart (S-04, S-05).
     */
    public function merge(int $userId, array $items): AnonymousResourceCollection
    {
        DB::transaction(function () use ($userId, $items) {
            foreach ($items as $payload) {
                $shopVariant = $this->purchasableVariants->resolve(
                    $payload['shop_product_variant_id'] ?? null,
                    $payload['quantity'] ?? null,
                    lockForUpdate: true,
                );

                $item = CartItem::query()->firstOrNew([
                    'user_id' => $userId,
                    'shop_product_variant_id' => $shopVariant->id,
                ]);

                $incoming = (int) $payload['quantity'];
                $nextQty = max($item->exists ? (int) $item->quantity : 0, $incoming);
                $this->purchasableVariants->assertQuantity($shopVariant, $nextQty);

                $item->quantity = $nextQty;
                // Keep what the user already typed; only fill a gap.
                if (array_key_exists('note', $payload) && $payload['note'] !== null
                    && ($item->note === null || $item->note === '')) {
                    $item->note = $payload['note'];
                }
                if (array_key_exists('extras', $payload) && $payload['extras'] !== null
                    && empty($item->extras)) {
                    $item->extras = $payload['extras'];
                }
                $this->applyCartContext($item, $payload);
                $item->save();
            }
        }, 3);

        return $this->list($userId);
    }

    /** Empty the cart on the server so every device sees it emptied (S-10). */
    public function clear(int $userId): bool
    {
        CartItem::query()->where('user_id', $userId)->delete();

        return true;
    }

    private function applyCartContext(CartItem $item, array $data): void
    {
        foreach ([
            'cart_type',
            'recipe_id',
            'admin_basket_id',
            'admin_schedule_basket_id',
            'basket_schedule_id',
        ] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $item->{$key} = $data[$key];
            }
        }
    }

    public function checkoutSummary(int $userId): array
    {
        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with(['shopProductVariant.productVariant.product', 'shopProductVariant.shop'])
            ->get();

        $subtotal = $items->sum(function (CartItem $item) {
            $price = (float) ($item->shopProductVariant?->productVariant?->final_price ?? 0);

            return $price * (int) $item->quantity;
        });

        $shops = $items
            ->map(fn (CartItem $item) => $item->shopProductVariant?->shop)
            ->filter()
            ->unique('id')
            ->values();

        $summary = $this->checkoutPolicy->summarize($this->checkoutPolicy->forShops($shops), $subtotal);
        $summary['delivery_price'] = $this->resolveCartDeliveryPrice($userId, $items);

        return $this->presentCheckout($summary);
    }

    public function presentCheckout(array $summary): array
    {
        $remaining = (float) $summary['remaining_amount'];

        $payload = [
            'source' => $summary['source'],
            'shop_id' => $summary['shop_id'],
            'min_order_amount' => $this->convertPrice($summary['min_order_amount']),
            'subtotal' => $this->convertPrice($summary['subtotal']),
            'remaining_amount' => $this->convertPrice($remaining),
            'can_checkout' => (bool) $summary['can_checkout'],
            'delivery_min_hours' => (int) $summary['delivery_min_hours'],
            'delivery_max_hours' => (int) $summary['delivery_max_hours'],
            'earliest_delivery_at' => $summary['earliest_delivery_at'],
            'instant_only' => (bool) ($summary['instant_only'] ?? false),
            'fulfillment' => $summary['fulfillment'] ?? null,
            'message' => $remaining > 0
                ? __('custom.checkout.min_order_remaining', [
                    'min' => $this->convertFormattedPrice($summary['min_order_amount']),
                    'remaining' => $this->convertFormattedPrice($remaining),
                ])
                : null,
        ];

        if (array_key_exists('delivery_price', $summary)) {
            $payload['delivery_price'] = $summary['delivery_price'] === null
                ? null
                : $this->convertPrice($summary['delivery_price']);
        }

        return $payload;
    }

    /**
     * Distance fee for the cart, using the customer's default address.
     * A restaurant is a shop: same fee, and free delivery returns 0.
     * Hours are not part of this number.
     */
    private function resolveCartDeliveryPrice(int $userId, Collection $items): ?float
    {
        if ($items->isEmpty()) {
            return 0.0;
        }

        $user = User::query()->find($userId);
        if (! $user || ! $user->addresses()->where('is_default', true)->exists()) {
            return null;
        }

        $variantIds = $items->pluck('shop_product_variant_id')->filter()->unique()->values()->all();
        if ($variantIds === []) {
            return null;
        }

        try {
            return CalculateDeliveryPriceService::handle($user, $variantIds);
        } catch (CustomExceptionWithMessage|ModelNotFoundException) {
            return null;
        }
    }

    public function add(int $userId, array $data): CartItemResource
    {
        return DB::transaction(function () use ($userId, $data) {
            $shopVariant = $this->purchasableVariants->resolve(
                $data['shop_product_variant_id'] ?? null,
                $data['quantity'] ?? null,
                lockForUpdate: true,
            );
            $quantity = (int) $data['quantity'];

            $item = CartItem::query()->firstOrNew([
                'user_id' => $userId,
                'shop_product_variant_id' => $shopVariant->id,
            ]);

            // "add" (default) keeps the product-page behaviour of summing.
            // "set" pins the quantity, so syncing a cart up is safe to repeat.
            $mode = $data['mode'] ?? 'add';
            $nextQty = $mode === 'set'
                ? $quantity
                : ($item->exists ? (int) $item->quantity : 0) + $quantity;
            $this->purchasableVariants->assertQuantity($shopVariant, $nextQty);

            $item->quantity = $nextQty;
            if (array_key_exists('note', $data)) {
                $item->note = $data['note'];
            }
            if (array_key_exists('extras', $data)) {
                $item->extras = $data['extras'];
            }
            $this->applyCartContext($item, $data);
            $item->save();

            return new CartItemResource($item->load($this->itemRelations()));
        }, 3);
    }

    public function update(int $userId, int $itemId, array $data): CartItemResource
    {
        return DB::transaction(function () use ($userId, $itemId, $data) {
            $item = $this->findItem($userId, $itemId);
            $this->purchasableVariants->resolve(
                $item->shop_product_variant_id,
                array_key_exists('quantity', $data) ? $data['quantity'] : $item->quantity,
                lockForUpdate: true,
            );

            if (array_key_exists('quantity', $data)) {
                $item->quantity = (int) $data['quantity'];
            }

            if (array_key_exists('note', $data)) {
                $item->note = $data['note'];
            }

            if (array_key_exists('extras', $data)) {
                $item->extras = $data['extras'];
            }

            $item->save();

            return new CartItemResource($item->load($this->itemRelations()));
        }, 3);
    }

    public function remove(int $userId, int $itemId): bool
    {
        $this->findItem($userId, $itemId)->delete();

        return true;
    }

    private function findItem(int $userId, int $itemId): CartItem
    {
        $item = CartItem::query()
            ->where('user_id', $userId)
            ->whereKey($itemId)
            ->first();

        if (!$item) {
            throw new NotFoundException();
        }

        return $item;
    }

    private function itemRelations(): array
    {
        return [
            // withTrashed on product: a soft-deleted product must still render
            // its name and image on the row flagged as unavailable.
            'shopProductVariant.productVariant.product' => fn ($q) => $q->withTrashed(),
            'shopProductVariant.productVariant.product.media',
            'shopProductVariant.productVariant.media',
            'shopProductVariant.shop',
        ];
    }
}
