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
            'product_variant_id' => $this->productVariant->id,
            'shop_product_variant_id' => $this->id,
            'shop_id' => $this->shop_id,
            'is_restaurant' => (bool) ($this->shop?->is_restaurant ?? false),
            'city_id' => $this->shop?->city_id ?? $this->shop?->area?->city_id,
            'name' => $this->productVariant->product->name,
            'sku' => $this->productVariant->sku,
            'model' => $this->productVariant->model,
            'barcode' => $this->productVariant->barcode,
            'image_url' => $this->productVariant->product->image_url,
            ...$this->withCurrency($this->price, 'price'),
            ...$this->withCurrency($this->discount, 'discount'),
            ...$this->withCurrency($this->price_after_discount, 'price_after_discount'),
            'variant' =>  $this->productVariant->attributes_values->pluck('name')->toArray(),

        ];
    }
}
