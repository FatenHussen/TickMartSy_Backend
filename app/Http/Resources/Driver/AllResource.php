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
            'status' => $this->status,
            'image' => $this->image_url,
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
