<?php

namespace App\Http\Resources\Driver;

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
            'email' => $this->email,
            'status' => $this->status,
            'image' => $this->image_url,
            'address' => $this->address,
            'rate_per_order' => (float) $this->rate_per_order,
            'is_active' => (bool) $this->is_active,

            // Vehicle information
            'vehicle_type' => $this->vehicle_type,
            'vehicle_name' => $this->vehicle_name,
            'vehicle_number' => $this->vehicle_number,
            'vehicle_image' => $this->vehicle_image_url,

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

            // Cities served
            'cities' => ($this->cities ?? collect())->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                ];
            })->values(),

            'shops' => ($this->shops ?? collect())->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                ];
            })->values(),

            'vendors' => ($this->vendors ?? collect())->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                ];
            })->values(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
