<?php

namespace App\Http\Resources\Admin\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' =>  $this->getTranslations('name'),
            'price' => (float) $this->price,
            'duration_days' => (int) $this->duration_days,

            // Features
            'monthly_orders_limit' => $this->monthly_orders_limit,
            'free_delivery_count' => (int) $this->free_delivery_count,
            'discount_percentage' => (float) $this->discount_percentage,
            'points_bonus' => (int) $this->points_bonus,

            // Status
            'is_active' => (bool) $this->is_active,

            // Statistics
            'subscriptions_count' => $this->subscriptions()->count(),
            'active_subscriptions_count' => $this->subscriptions()->where('status', 'active')->count(),
            'expired_subscriptions_count' => $this->subscriptions()->where('status', 'expired')->count(),
            'cancelled_subscriptions_count' => $this->subscriptions()->where('status', 'cancelled')->count(),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
