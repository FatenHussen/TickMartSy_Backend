<?php

namespace App\Http\Resources\Admin\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'basket_id' => $this->basket_id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'shop_product_variant_ids' => $this->shop_product_variant_ids,
            
            // Product details
            'product' => $this->whenLoaded('product', [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'sku' => $this->product?->sku,
                'image' => $this->product?->image_url,
            ]),
            
            // Variant details
            'variant' => $this->whenLoaded('variant', [
                'id' => $this->variant?->id,
                'sku' => $this->variant?->sku,
            ]),
            
            // Shop variant details
            'shop_variant' => $this->whenLoaded('shopProductVariant', [
                'id' => $this->shopProductVariant?->id,
                'shop_id' => $this->shopProductVariant?->shop_id,
                'shop_name' => $this->shopProductVariant?->shop?->name,
                'price' => $this->shopProductVariant?->price,
                'quantity' => $this->shopProductVariant?->quantity,
            ]),
            
            // Quantities and pricing
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => $this->subtotal,
            
            // Constraints
            'is_required' => (bool) $this->is_required,
            'is_extra' => (bool) $this->is_extra,
            'min_quantity' => (int) $this->min_quantity,
            'max_quantity' => (int) $this->max_quantity,
            'can_adjust' => $this->canAdjustQuantity(),
            
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}