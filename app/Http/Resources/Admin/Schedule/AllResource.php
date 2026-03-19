<?php

namespace App\Http\Resources\Admin\Schedule;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'interval_days' => $this->interval_days,
            'is_active' => $this->is_active,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ? (float) $this->discount_value : null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
