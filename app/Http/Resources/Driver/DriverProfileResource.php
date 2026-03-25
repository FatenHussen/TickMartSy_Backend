<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status,
            'image' => $this->image_url,
            'address' => $this->address,
            'rate_per_order' => (float) $this->rate_per_order,
            'is_active' => (bool) $this->is_active,

            // Vehicle information
            'vehicle_type' => $this->vehicle_type,
            'vehicle_number' => $this->vehicle_number,

            // Statistics
            'average_rating' => $this->average_rating,
            'total_orders' => $this->total_orders,
            'completed_orders' => $this->total_delivered,
            'total_delivered' => $this->total_delivered,
            'today_delivered' => $this->today_delivered,
            'total_earnings' => $this->total_earnings,
            'today_earnings' => $this->today_earnings,
            'average_delivery_time_minutes' => $this->average_delivery_time,
            // 'cancellation_rate_percent' => $this->cancellation_rate,

            // Areas served
            'areas' => $this->areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
