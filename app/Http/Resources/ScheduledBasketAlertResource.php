<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledBasketAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'basket_type' => $this->basket_type,
            'basket_reference_id' => $this->basket_reference_id,
            'basket_schedule_id' => $this->basket_schedule_id,
            'order_id' => $this->order_id,
            'alert_type' => $this->alert_type,
            'status' => $this->status,
            'user_decision' => $this->user_decision,
            'payload' => $this->payload ?? [],
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),
            'first_detected_at' => $this->first_detected_at?->format('Y-m-d H:i:s'),
            'last_detected_at' => $this->last_detected_at?->format('Y-m-d H:i:s'),
            'last_notified_at' => $this->last_notified_at?->format('Y-m-d H:i:s'),
            'resolved_at' => $this->resolved_at?->format('Y-m-d H:i:s'),
            'decision_at' => $this->decision_at?->format('Y-m-d H:i:s'),
        ];
    }
}
