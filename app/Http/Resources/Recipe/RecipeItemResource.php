<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Governorate\AllResource;
use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->shopProductVariant->productVariant->product->id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'name' => $this->shopProductVariant->productVariant->product->name,
            'image' => $this->image,
            'is_required' => $this->is_required,
            'default_quantity' => $this->quantity,
            'min_quantity' => $this->min_quantity ??  $this->quantity,
            'max_quantity' => $this->max_quantity ??  $this->quantity,
            'price' => $this->shopProductVariant->price,
        ];
    }
}
