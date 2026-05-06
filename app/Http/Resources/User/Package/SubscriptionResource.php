<?php

namespace App\Http\Resources\User\Package;

use App\Http\Resources\PaymentMethod\PaymentMethodResource;
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
            'payment_method_id' => $this->payment_method_id,
            'payment_method' => $this->paymentMethod
                ? new PaymentMethodResource($this->paymentMethod)
                : null,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'remaining_orders' => $this->remaining_orders,
            'remaining_free_deliveries' => $this->remaining_free_deliveries,
        ];
    }
}
