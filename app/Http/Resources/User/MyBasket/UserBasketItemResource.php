<?php

namespace App\Http\Resources\User\MyBasket;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\Basket\BasketItemVariantResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBasketItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => round($this->price * $this->quantity, 2),
            'is_required' => true,
            'is_extra' => false,
            'min_quantity' => 1,
            'max_quantity' => 99,
            'can_adjust' => true,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'product' => new BasketItemProductResource($this->whenLoaded('product')) ?? null,
            'variant' => $this->variant && $this->variant->productVariant
                ? new BasketItemVariantResource($this->variant->productVariant)
                : null,
            'alternatives' => [],
        ];
    }
}
