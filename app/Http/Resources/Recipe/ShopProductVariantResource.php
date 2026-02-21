<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Governorate\AllResource;
use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductVariantResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        return [
            'product_id' => $this->productVariant->product->id,
            'shop_product_variant_id' => $this->id,
            'name' => $this->productVariant->product->name,
            'image_url' => $this->productVariant->product->image_url,
            ...$this->withCurrency($this->price, 'price'),
            'variant' =>  $this->productVariant->attributes_values->pluck('name')->toArray(),

        ];
    }
}
