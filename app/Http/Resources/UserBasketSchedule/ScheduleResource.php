<?php

namespace App\Http\Resources\UserBasketSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'interval_days' => $this->interval_days,
            'discount_type' => $this->discount_type,
            'discount_value' => round($this->discount_value, 2),
            'is_active' => $this->is_active,
        ];
    }
}
