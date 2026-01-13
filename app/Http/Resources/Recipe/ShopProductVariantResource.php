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
            'product_id' => $this->productVariant->product->id,
            'shop_product_variant_id' => $this->id,
            'name' => $this->productVariant->product->name,
            'image_url' => $this->productVariant->product->image_url,
            'price' => $this->price,
        ];
    }
}
