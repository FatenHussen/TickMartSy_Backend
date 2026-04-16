<?php

namespace App\Http\Resources\Admin\DriverWalletTransaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'driver_id' => $this->driver_id,
            'driver' => [
                'id' => $this->driver?->id,
                'name' => $this->driver?->name,
                'email' => $this->driver?->email,
                'phone' => $this->driver?->phone,
                'image_url' => $this->driver?->image_url,
            ],
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'delivery_fee' => (float) $this->delivery_fee,
            'rate_percent' => (float) $this->rate_percent,
            'order_id' => $this->order_id,
            'order_number' => $this->order?->id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
