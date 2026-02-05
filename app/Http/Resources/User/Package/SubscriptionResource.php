<?php

namespace App\Http\Resources\User\Package;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'package' => new PackageResource($this->package),
            'status' => $this->status,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'remaining_orders' => $this->remaining_orders,
            'remaining_free_deliveries' => $this->remaining_free_deliveries,
        ];
    }
}