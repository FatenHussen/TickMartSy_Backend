<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\UserBasketSchedule\BasketItemVariantResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'price' => $this->price,
            'shop_product_variant_id' => $this->shop_product_variant_id,

            'product' => new BasketItemProductResource($this->whenLoaded('product')),

            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')),
        ];
    }
}
