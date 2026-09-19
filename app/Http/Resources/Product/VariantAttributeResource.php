<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantAttributeResource extends JsonResource
{
    public function toArray($request)
    {
        $isColorType = ($this->categoryAttribute?->type ?? null) === 'color';

        return [
            'id'        => $this->id,
            'attribute' => $this->categoryAttribute?->name,
            'value'     => $isColorType
                ? ($this->color?->name ?? $this->name)
                : $this->name,
            'type'      => $this->categoryAttribute?->type,
            'hex'       => $isColorType ? ($this->color?->hex ?? null) : null,
        ];
    }
}
