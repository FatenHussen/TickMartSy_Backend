<?php

namespace App\Http\Resources\User\Package;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'monthly_orders_limit' => $this->monthly_orders_limit,
            'free_delivery_count' => $this->free_delivery_count,
            'discount_percentage' => $this->discount_percentage,
            'points_bonus' => $this->points_bonus,
            'is_active' => $this->is_active,
        ];
    }
}
