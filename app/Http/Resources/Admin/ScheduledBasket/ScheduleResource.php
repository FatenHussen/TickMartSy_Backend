<?php

namespace App\Http\Resources\Admin\ScheduledBasket;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'number_of_days' => (int) $this->number_of_days,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
