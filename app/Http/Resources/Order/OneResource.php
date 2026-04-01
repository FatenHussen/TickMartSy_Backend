<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Address\AllResource as AddressOneResource;
use App\Http\Resources\Basket\AllResource as BasketAllResource;
use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;
use App\Http\Resources\Driver\AllResource as DriverAllResource;
use App\Http\Resources\EndUser\AllResource;
use App\Http\Resources\SectionItem\AllResource as SectionItemAllResource;
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
            'order_code' => $this->order_code ?? $this->id,
            'status' => $this->status,
            'cart_type' => $this->cart_type,
            'is_instant_delivery' => $this->is_instant_delivery,
            'delivery_price' => $this->delivery_price,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'total_quantity' => $this->total_quantity,
            'basket_discount' => $this->basket_discount,
            'coupon_discount' => $this->coupon_discount,
            'promotion_discount' => $this->promotion_discount,
            'subscription_discount' => $this->subscription_discount ?? 0,
            'coupon_discount_from_points' => $this->coupon_discount_from_points ?? 0,

            'assigned_by' => $this->assigned_by,

            // Point exchanges used
            'free_delivery_from_points' => $this->free_delivery_from_points ?? false,
            'use_coupon_exchange_id' => $this->used_coupon_exchange_id,
            'use_free_delivery_exchange_id' => $this->used_free_delivery_exchange_id,

            // Subscription benefits used

            'subscription_free_delivery' => $this->subscription_free_delivery ?? false,


            'created_at' => $this->created_at?->toDateTimeString(),
            'affiliate' => [
                'affiliate_rate' => $this->affiliate_rate,
                'affiliate_source' => $this->affiliate_source,
                'affiliate_commission' => $this->affiliate_commission,
            ],
            'timestamps' => [
                'pending_at' => $this->pending_at,
                'preparing_at' => $this->preparing_at,
                'out_delivery_at' => $this->out_delivery_at,
                'delivered_at' => $this->delivered_at,
            ],
            'user' => AllResource::make($this->user),
            'driver' => DriverAllResource::make($this->driver),
            'user_address' => AddressOneResource::make($this->address),
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'icon' => $this->paymentMethod->icon,

            ] : null,
            // 'baskes' => $this->basket ? BasketAllResource::make($this->basket) : null,
            // 'basket_schedule' => $this->basket_schedule_id ? BasketScheduleAllResource::make($this->basketSchedule) : null,
            'items' => OrderItemResource::collection(
                $this->items
            ),
        ];
    }
}
