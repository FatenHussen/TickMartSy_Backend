<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Governorate\AllResource;
use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'shop_product_variant_id' => $this->id,
            'product_id' => $this->productVariant->product->id,
            'name' => $this->productVariant->product->name,
            'price' => $this->price,
            'shop_id' => $this->shop_id,
            'shop_name' => $this->shop->name,
        ];
    }
}
