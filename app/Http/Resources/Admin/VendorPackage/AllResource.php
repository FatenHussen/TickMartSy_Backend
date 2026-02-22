<?php

namespace App\Http\Resources\Admin\VendorPackage;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'duration_days' => (int) $this->duration_days,
            'max_products' => (int) $this->max_products,
            'commission_rate' => (float) $this->commission_rate,
            'is_active' => (bool) $this->is_active,
            'active_subscriptions_count' => $this->subscriptions()->where('status', 'active')->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
