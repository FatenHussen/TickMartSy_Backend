<?php

namespace App\Http\Resources\UserBasketSchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),

            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'items' => BasketItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
