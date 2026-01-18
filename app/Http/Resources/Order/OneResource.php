<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->order_status,
            'cart_type' => $this->cart_type,
            'is_instant_delivery' => $this->is_instant_delivery,
            'delivery_price' => $this->delivery_price,
            'coupon_discount' => $this->coupon_discount,
            'total_quantity' => $this->total_quantity,
            'total' => $this->total,

            'created_at' => $this->created_at?->toDateTimeString(),

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),
        ];
    }
}
