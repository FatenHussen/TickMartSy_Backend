<?php

namespace App\Http\Resources\Driver;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'total_orders' => $this->orders()->count(),
            'completed_orders' => $this->completedOrders()->count(),
            'total_earnings' => $this->total_earnings,

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
