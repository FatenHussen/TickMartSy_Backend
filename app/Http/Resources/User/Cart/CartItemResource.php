<?php

namespace App\Http\Resources\User\Cart;

use App\Enums\ProductApprovalStatus;
use App\Http\Resources\Product\VariantAttributeResource;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The cart follows the account, not the device, so this payload has to be
 * enough on its own to draw the cart screen on a device that never visited
 * the product page. Prices are always the server's current numbers: the cart
 * is not a price lock.
 */
class CartItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        $shopVariant = $this->shopProductVariant;
        $productVariant = $shopVariant?->productVariant;
        $product = $productVariant?->product;
        $shop = $shopVariant?->shop;

        // Stock lives on the product variant, which the shop rows share.
        // Never fall back to the parent product's quantity: the frontend caps
        // the quantity stepper with this number.
        $availableQuantity = $productVariant?->quantity === null
            ? null
            : (int) $productVariant->quantity;

        $unavailableReason = $this->resolveUnavailableReason();
        $quantity = (int) $this->quantity;

        $unitPrice = (float) ($productVariant?->final_price ?? 0);

        return [
            'id' => $this->id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'product_id' => $product?->id,
            'shop_id' => $shopVariant?->shop_id,
            'shop' => $shop ? [
                'id' => $shop->id,
                'name' => $shop->name,
            ] : null,

            'name' => $product?->name,
            'image' => $this->resolveImage($productVariant, $product),

            'quantity' => $quantity,
            'note' => $this->note,
            'extras' => $this->extras ?? [],

            // Server-computed. The frontend renders `formatted` as-is.
            'unit_price' => $this->convertPrice($unitPrice),
            'line_total' => $this->convertPrice($unitPrice * $quantity),

            // Variant SKU first, product SKU as fallback, null when neither exists.
            'sku' => $productVariant?->sku ?? $product?->sku,
            'model' => $productVariant?->model ?? $product?->model,
            'available_quantity' => $availableQuantity,

            'is_instant_delivery' => (bool) ($product?->is_instant_delivery ?? false),
            'category_id' => $product?->category_id,

            // Same shape and ids as shop_variants[].attributes on the product page.
            'attributes' => $productVariant
                ? VariantAttributeResource::collection(
                    AttributeValue::query()
                        ->with(['categoryAttribute', 'color'])
                        ->whereIn('id', $productVariant->attributes_values_ids ?? [])
                        ->get()
                )
                : [],

            // A row that cannot be ordered must never arrive silently: the user
            // would otherwise reach checkout and be refused without a reason.
            'unavailable' => $unavailableReason !== null,
            'unavailable_reason' => $unavailableReason,

            // Kept for backwards compatibility with existing clients.
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
            ] : null,
            'stock_quantity' => $availableQuantity,
        ];
    }

    private function resolveImage($productVariant, $product): ?string
    {
        return $productVariant?->media?->first(fn ($image) => $image->isUsable())?->url
            ?? $product?->media?->first(fn ($image) => $image->isUsable())?->url
            ?? $product?->thumbnail_url;
    }

    /**
     * Why this row cannot be ordered, or null when it can.
     * Mirrors the checks PurchasableVariantService enforces on write.
     */
    private function resolveUnavailableReason(): ?string
    {
        $shopVariant = $this->shopProductVariant;
        $productVariant = $shopVariant?->productVariant;
        $product = $productVariant?->product;

        if (! $shopVariant || ! $productVariant || ! $product) {
            return __('custom.cart_quantity_unavailable');
        }

        if ($productVariant->trashed() || ! $productVariant->is_active
            || $product->trashed() || ! $product->is_active || ! $product->is_visible
            || $product->approval_status !== ProductApprovalStatus::APPROVED
            || in_array((int) $product->category_id, Category::idsHiddenFromCatalog(), true)) {
            return __('custom.cart_quantity_unavailable');
        }

        if (! $shopVariant->shop || ! $shopVariant->shop->is_active) {
            return __('custom.cart_quantity_unavailable');
        }

        if ((int) ($productVariant->quantity ?? 0) < 1) {
            return __('custom.cart_quantity_unavailable');
        }

        return null;
    }
}
