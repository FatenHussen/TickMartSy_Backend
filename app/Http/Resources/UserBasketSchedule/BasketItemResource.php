<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\UserBasketSchedule\BasketItemVariantResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            ...$this->withCurrency($this->price, 'price'),
            'shop_product_variant_id' => $this->shop_product_variant_id,

            'product' => new BasketItemProductResource($this->whenLoaded('product')),

            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')),
        ];
    }
}
