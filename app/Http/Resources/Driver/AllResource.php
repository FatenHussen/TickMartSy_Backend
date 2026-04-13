<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'image' => $this->image_url,
            'vehicle_name' => $this->vehicle_name,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_image' => $this->vehicle_image_url,
            'rate_per_order' => (float) $this->rate_per_order,
            'is_active' => (bool) $this->is_active,
            // Statistics
            'average_rating' => $this->average_rating,
            'total_orders' => $this->orders()->count(),
            'completed_orders' => $this->completedOrders()->count(),
            'total_earnings' => $this->total_earnings,

            'created_at' => $this->created_at,
        ];
    }
}
