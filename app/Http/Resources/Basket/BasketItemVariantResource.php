<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return $this->attributes_values->pluck('name')->toArray();
    }
}
