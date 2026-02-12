<?php

namespace App\Http\Resources\Admin\UserBasketSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            // Product info
            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'image' => $this->product?->media?->first()?->url,
            ],

            // Variant info
            'variant' => $this->variant ? [
                'id' => $this->variant->id,
                'shop_id' => $this->variant->shop_id,
                'price' => $this->variant->price,
                'quantity' => $this->variant->quantity,
            ] : null,

            // Item details
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => round($this->price * $this->quantity, 2),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
