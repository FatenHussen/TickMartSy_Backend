<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\ScheduledBasketAlertResource;
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
            'is_paused' => $this->isPaused(),
            'paused_at' => $this->paused_at?->format('Y-m-d H:i:s'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),
            'availability' => $this->availability_summary ? [
                'status' => $this->availability_summary['status'],
                'has_issue' => $this->availability_summary['has_issue'],
                'unavailable_items_count' => $this->availability_summary['unavailable_items_count'],
                'warning_items_count' => $this->availability_summary['warning_items_count'],
            ] : null,
            'active_alert' => $this->active_alert
                ? ScheduledBasketAlertResource::make($this->active_alert)
                : null,
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'items' => BasketItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
