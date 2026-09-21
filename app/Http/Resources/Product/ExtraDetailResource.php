<?php

namespace App\Http\Resources\Product;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class ExtraDetailResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        $price = (float) ($this->pivot->price ?? 0);

        return [
            'id' => $this->id,
            // Add-on name (not a free-text attribute like "Cotton")
            'key' => $this->getTranslations('detail_key') ?? [],
            // Optional description — may be empty
            'value' => $this->getTranslations('detail_value') ?? [],
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'quantity' => (int) ($this->pivot->quantity ?? 0),
            'price' => $price,
            'price_currencies' => $this->dualCurrency($price),
        ];
    }
}
