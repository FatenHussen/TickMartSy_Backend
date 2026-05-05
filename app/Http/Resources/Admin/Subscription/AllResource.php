<?php

namespace App\Http\Resources\Admin\Subscription;

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
                'email' => $this->user?->email,
            ],
            'package' => [
                'id' => $this->package?->id,
                'name' => $this->package?->name,
                'price' => $this->package?->price,
            ],
            'payment_method' => $this->paymentMethod
                ? [
                    'id' => $this->paymentMethod->id,
                    'name' => $this->paymentMethod->name,
                    'code' => $this->paymentMethod->code,
                ]
                : null,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'remaining_orders' => $this->remaining_orders,
            'remaining_free_deliveries' => $this->remaining_free_deliveries,
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
