<?php

namespace App\Http\Resources\Admin\Basket;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\Basket\BasketItemVariantResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'shop_product_variant_id' => $this->shop_product_variant_id,

            // Product details
            'product' => $this->product ? new BasketItemProductResource($this->product) : null,

            // Variant details
            'variant' => $this->variant ? new BasketItemVariantResource($this->variant) : null,

            // Shop variant details
            'shop_variant' => $this->shopProductVariant ? [
                'id' => $this->shopProductVariant->id,
                'shop_id' => $this->shopProductVariant->shop_id,
                'shop_name' => $this->shopProductVariant->shop?->name,
                'price' => $this->shopProductVariant->price,
                'quantity' => $this->shopProductVariant->quantity,
            ] : null,

            // Quantities and pricing
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => $this->subtotal,
            'is_active' => $this->is_active,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
