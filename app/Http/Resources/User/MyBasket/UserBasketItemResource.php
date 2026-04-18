<?php

namespace App\Http\Resources\User\MyBasket;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\Basket\BasketItemVariantResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            ...$this->withCurrency($this->price, 'unit_price'),
            ...$this->withCurrency($this->price * $this->quantity, 'subtotal'),
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
