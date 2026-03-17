<?php

namespace App\Http\Resources\Admin\UserBasketSchedule;

use App\Http\Resources\UserBasketSchedule\BasketItemResource;
use App\Http\Resources\UserBasketSchedule\ScheduleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            // User info (additional for admin)
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
                'image' => $this->user?->image_url,
            ],

            // Basket info (same as user resource)
            'name' => $this->name,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),

            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'items' => BasketItemResource::collection($this->whenLoaded('items')),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
