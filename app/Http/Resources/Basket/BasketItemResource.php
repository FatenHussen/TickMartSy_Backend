<?php

namespace App\Http\Resources\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => $this->subtotal,
            'is_required' => $this->is_required,
            'min_quantity' => (int) $this->min_quantity,
            'max_quantity' => (int) $this->max_quantity,
            'can_adjust' => $this->canAdjustQuantity(),

            'product' =>new BasketItemProductResource($this->whenLoaded('product')) ?? null,

            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')) ?? null,

            'companies' => BasketItemCompanyResource::collection($this->whenLoaded('companies')) ??  [],
        ];
    }
}
