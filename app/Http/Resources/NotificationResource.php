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
        return [
            'id'         => $this->id,
            'title'      => $this->data['title'] ?? null,
            'body'       => $this->data['body'] ?? null,
            'type'       => $this->data['data']['type'] ?? null,

            'is_fixed'       => $this->data['data']['is_fixed'] ?? 0,
            'read'    => $this->read_at ? true : false,
            'created_at' => $this->created_at->diffForHumans(),

        ];
    }
}
