<?php

namespace App\Http\Resources\Admin\PointExchange;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'exchange_type' => $this->exchange_type,
            'status' => $this->status,
            'points_used' => $this->transaction?->points,
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
