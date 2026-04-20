<?php

namespace App\Http\Resources\UserBasketSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        $productVariant = $this->productVariant;

        return [
            'name' => $productVariant?->attributes_values->pluck('name')->toArray() ?? [],
            'sku' => $productVariant?->sku,
            'model' => $productVariant?->model,
            'barcode' => $productVariant?->barcode,
        ];
    }
}
