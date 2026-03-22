<?php

namespace App\Http\Resources\Admin\Schedule;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'interval_days' => $this->interval_days,
            'is_active' => $this->is_active,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ? (float) $this->discount_value : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
