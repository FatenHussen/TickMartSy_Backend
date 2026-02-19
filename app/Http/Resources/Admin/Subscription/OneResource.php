<?php

namespace App\Http\Resources\Admin\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'package' => [
                'id' => $this->package?->id,
                'name' => $this->package?->name,
                'price' => $this->package?->price,
                'duration_days' => $this->package?->duration_days,
                'monthly_orders_limit' => $this->package?->monthly_orders_limit,
                'free_delivery_count' => $this->package?->free_delivery_count,
                'discount_percentage' => $this->package?->discount_percentage,
                'points_bonus' => $this->package?->points_bonus,
            ],
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'remaining_orders' => $this->remaining_orders,
            'remaining_free_deliveries' => $this->remaining_free_deliveries,
            'is_active' => $this->isActive(),
            'days_remaining' => $this->end_date ? now()->diffInDays($this->end_date, false) : null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
