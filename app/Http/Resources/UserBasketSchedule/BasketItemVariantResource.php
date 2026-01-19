<?php

namespace App\Http\Resources\UserBasketSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->productVariant->attributes_values->pluck('name')->toArray(),
        ];
    }
}
