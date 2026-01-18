<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantAttributeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'attribute' => $this->categoryAttribute?->name,
            'value'     => $this->name,
            'type'      => $this->categoryAttribute->type,
        ];
    }
}
