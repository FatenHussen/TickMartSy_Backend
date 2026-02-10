<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Driver\AllResource as DriverAllResource;
use App\Http\Resources\EndUser\AllResource;
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
            'status' => $this->status,
            'cart_type' => $this->cart_type,
            'is_instant_delivery' => $this->is_instant_delivery,
            'delivery_price' => $this->delivery_price,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'total_with_delivery' =>  $this->total + $this->delivery_price,
            'total_quantity' => $this->total_quantity,
            'basket_discount' => $this->basket_discount,
            'coupon_discount' => $this->coupon_discount,
            'created_at' => $this->created_at?->toDateTimeString(),
            'assigned_by' => $this->assigned_by,
            'affiliate_rate' => $this->affiliate_rate,
            'affiliate_source' => $this->affiliate_source,
            'affiliate_commission' => $this->affiliate_commission,
            'user' => AllResource::make($this->user),
            'driver' => DriverAllResource::make($this->driver),

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),
        ];
    }
}
