<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\EndUser\AllResource as EndUserAllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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
            'order_code' => $this->order_code ?? $this->id,
            'status' => $this->status,
            'cart_type' => $this->cart_type,
            'is_instant_delivery' => $this->is_instant_delivery,
            'delivery_price' => $this->delivery_price,
            'total' => $this->total,
            'subtotal' => $this->subtotal,
            // 'total_with_delivery' =>  $this->total + $this->delivery_price,
            'total_quantity' => $this->total_quantity,
            'basket_discount' => $this->basket_discount,
            'coupon_discount' => $this->coupon_discount,

            // Subscription benefits used
            'subscription_discount' => $this->subscription_discount ?? 0,
            'subscription_free_delivery' => $this->subscription_free_delivery ?? false,

            'created_at' => $this->created_at?->toDateTimeString(),
            'assigned_by' => $this->assigned_by,
            'affiliate_rate' => $this->affiliate_rate,
            'affiliate_source' => $this->affiliate_source,
            'affiliate_commission' => $this->affiliate_commission,
            'user' => EndUserAllResource::make($this->user),

        ];
    }
}
