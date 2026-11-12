<?php

namespace App\Http\Resources\User\Cart;

use App\Http\Resources\Product\VariantAttributeResource;
use App\Models\AttributeValue;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $shopVariant = $this->shopProductVariant;
        $productVariant = $shopVariant?->productVariant;
        $product = $productVariant?->product;

        // Stock lives on the product variant, which the shop rows share.
        // Never fall back to the parent product's quantity: the frontend caps
        // the quantity stepper with this number.
        $availableQuantity = $productVariant?->quantity === null
            ? null
            : (int) $productVariant->quantity;

        return [
            'id' => $this->id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'shop_id' => $shopVariant?->shop_id,
            'product_id' => $product?->id,
            'quantity' => $this->quantity,
            'note' => $this->note,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
            ] : null,
            // Variant SKU first, product SKU as fallback, null when neither exists.
            'sku' => $productVariant?->sku ?? $product?->sku,
            'model' => $productVariant?->model ?? $product?->model,
            'available_quantity' => $availableQuantity,
            // Same shape and ids as shop_variants[].attributes on the product page.
            'attributes' => $productVariant
                ? VariantAttributeResource::collection(
                    AttributeValue::query()
                        ->with(['categoryAttribute', 'color'])
                        ->whereIn('id', $productVariant->attributes_values_ids ?? [])
                        ->get()
                )
                : [],
            // Kept for backwards compatibility with existing clients.
            'stock_quantity' => $availableQuantity,
        ];
    }
}
