<?php

namespace App\Http\Resources\Admin\PointExchange;

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
            'transaction' => [
                'id' => $this->transaction?->id,
                'points' => $this->transaction?->points,
                'source' => $this->transaction?->source,
            ],
            'exchange_type' => $this->exchange_type,
            'exchange_data' => $this->exchange_data,
            'exchange_details' => $this->exchange_details,
            'status' => $this->status,
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
