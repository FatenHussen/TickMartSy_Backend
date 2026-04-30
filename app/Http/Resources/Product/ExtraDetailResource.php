<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtraDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'key' => $this->getTranslations('detail_key') ?? [],
            'value' => $this->getTranslations('detail_value') ?? [],
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'quantity' => (int) $this->pivot->quantity,
            'price' => (float) $this->pivot->price,
        ];
    }
}
