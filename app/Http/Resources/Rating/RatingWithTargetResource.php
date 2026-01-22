<?php

namespace App\Http\Resources\Rating;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingWithTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'type' => $this->type, 
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

            'target' => $this->whenLoaded('rateable', function () {
                return [
                    'id' => $this->rateable->id,
                    'name' => $this->rateable->name ?? null,
                    'image' => $this->rateable->image ?? null,
                ];
            }),
        ];
    }
}
