<?php

namespace App\Http\Resources\Admin\Basket;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\Basket\BasketItemVariantResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'variant_sku' => $this->variant?->sku,
            'variant_model' => $this->variant?->model,
            'variant_barcode' => $this->variant?->barcode,

            // Product details
            'product' => $this->product ? new BasketItemProductResource($this->product) : null,

            // Variant details
            'variant' => $this->variant ? new BasketItemVariantResource($this->variant) : null,

            // Shop variant details
            'shop_variant' => $this->shopProductVariant ? [
                'id' => $this->shopProductVariant->id,
                'shop_id' => $this->shopProductVariant->shop_id,
                'shop_name' => $this->shopProductVariant->shop?->name,
                'is_restaurant' => (bool) ($this->shopProductVariant->shop?->is_restaurant ?? false),
                'city_id' => $this->shopProductVariant->shop?->city_id ?? $this->shopProductVariant->shop?->area?->city_id,
                'price' => $this->shopProductVariant->price,
                'price_currencies' => $this->dualCurrency($this->shopProductVariant->price),
                'discount' => $this->shopProductVariant->discount,
                'discount_currencies' => $this->dualCurrency($this->shopProductVariant->discount),
                'price_after_discount' => $this->shopProductVariant->price_after_discount,
                'price_after_discount_currencies' => $this->dualCurrency($this->shopProductVariant->price_after_discount),
                'quantity' => $this->shopProductVariant->quantity,
            ] : null,

            // Quantities and pricing
            'quantity' => (int) $this->quantity,
            ...$this->withCurrency($this->price, 'unit_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            'is_active' => $this->is_active,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
