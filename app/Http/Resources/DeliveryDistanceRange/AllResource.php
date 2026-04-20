<?php

namespace App\Http\Resources\DeliveryDistanceRange;

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
            'min_distance' => (float) $this->min_distance,
            'max_distance' => $this->max_distance !== null ? (float) $this->max_distance : null,
            'multiplier' => (float) $this->multiplier,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
