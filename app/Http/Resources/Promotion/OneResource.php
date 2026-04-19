<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'type' => $this->type,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'min_spend' => $this->min_spend,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,
            'gift_description' => $this->getTranslations('gift_description'),
            'reward_points' => $this->reward_points,
        ];
    }
}
