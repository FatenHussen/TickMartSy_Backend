<?php

namespace App\Http\Resources\Admin\VendorSubscription;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
            ],
            'package' => $this->package ? [
                'id' => $this->package->id,
                'name' => $this->package->name,
                'max_products' => $this->package->max_products,
                'commission_rate' => $this->package->commission_rate,
            ] : null,
            'starts_at' => $this->starts_at?->format('Y-m-d'),
            'ends_at' => $this->ends_at?->format('Y-m-d'),
            'auto_renew' => (bool) $this->auto_renew,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'notified_at' => $this->notified_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
