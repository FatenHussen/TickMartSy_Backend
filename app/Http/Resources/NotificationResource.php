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
            'is_fixed'   => $payload['is_fixed'] ?? 0,
            'emoji'      => $payload['emoji'] ?? null,
            'media'      => $this->extractMedia($payload),
            'payload'    => $payload,
            'read'       => $this->read_at ? true : false,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }

    private function extractMedia(array $payload): ?array
    {
        if (! empty($payload['media']['url'])) {
            return [
                'type'          => $payload['media']['type'] ?? 'image',
                'url'           => $payload['media']['url'],
                'thumbnail_url' => $payload['media']['thumbnail_url'] ?? null,
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
