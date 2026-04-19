<?php

namespace App\Http\Resources\Admin\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'duration_days' => (int) $this->duration_days,
            'monthly_orders_limit' => $this->monthly_orders_limit,
            'free_delivery_count' => (int) $this->free_delivery_count,
            'discount_percentage' => (float) $this->discount_percentage,
            'points_bonus' => (int) $this->points_bonus,
            'is_active' => (bool) $this->is_active,
            'subscriptions_count' => $this->subscriptions()->count(),
            'active_subscriptions_count' => $this->subscriptions()->where('status', 'active')->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
