<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = $this->data['data'] ?? [];

        return [
            'id'         => $this->id,
            'title'      => $this->data['title'] ?? null,
            'body'       => $this->data['body'] ?? null,
            'type'       => $payload['type'] ?? null,
            'read'       => $this->read_at ? true : false,
            // 'is_fixed'   => $payload['is_fixed'] ?? 0,
            // 'emoji'      => $payload['emoji'] ?? null,
            // 'media'      => $this->extractMedia($payload),
            'payload'    => $payload,
            // 'type'       => $this->data['data']['type'] ?? null,
            // 'target_screen' => $this->data['data']['target_screen'] ?? null,
            // 'alert_id' => $this->data['data']['alert_id'] ?? null,
            // 'basket_id' => $this->data['data']['basket_id'] ?? null,
            // 'scheduled_basket_id' => $this->data['data']['scheduled_basket_id'] ?? null,
            // 'basket_schedule_id' => $this->data['data']['basket_schedule_id'] ?? null,
            // 'order_id' => $this->data['data']['order_id'] ?? null,
            // 'next_run_date' => $this->data['data']['next_run_date'] ?? null,
            // 'data' => $this->data['data'] ?? [],
            // 'read'    => $this->read_at ? true : false,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }

    private function extractMedia(array $payload): ?array
    {
        if (! empty($payload['media']['url'])) {
            return [
                'type'          => $payload['media']['type'] ?? 'image',
                'url'           => $payload['media']['url'],
            ];
        }

        if (! empty($payload['gif'])) {
            return [
                'type' => 'gif',
                'url'  => $payload['gif'],
            ];
        }

        if (! empty($payload['image'])) {
            return [
                'type' => 'image',
                'url'  => $payload['image'],
            ];
        }

        return null;
    }
}
