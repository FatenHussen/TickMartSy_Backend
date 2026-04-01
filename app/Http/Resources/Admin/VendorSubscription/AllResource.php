<?php

namespace App\Http\Resources\Admin\VendorSubscription;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->vendor?->name,
            'package' => [
                'id' => $this->package?->id,
                'name' => $this->package?->name,
            ],
            'starts_at' => $this->starts_at?->format('Y-m-d'),
            'ends_at' => $this->ends_at?->format('Y-m-d'),
            'auto_renew' => (bool) $this->auto_renew,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
