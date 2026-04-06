<?php

namespace App\Http\Resources\Rating;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'type' => $this->type,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'image' => $this->user?->image_url
            ],

            // 'rateable' => $this->whenLoaded('rateable'),
        ];
    }
}
