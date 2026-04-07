<?php

namespace App\Http\Resources\AdminNotification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'target_page' => $this->target_page,
            'channels' => $this->channels,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'emoji' => $this->emoji,
            'media' => $this->mediaPayload(),
        ];
    }

    private function mediaPayload(): ?array
    {
        if (empty($this->media_url)) {
            return null;
        }

        return array_filter([
            'type' => $this->media_type ?? 'image',
            'url' => $this->media_url,
        ], fn($value) => !is_null($value));
    }
}
