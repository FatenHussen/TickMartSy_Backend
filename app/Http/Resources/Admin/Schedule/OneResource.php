<?php

namespace App\Http\Resources\Admin\Schedule;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'image' => $this->image_url,
            'images' => $this->image_urls,
            'interval_days' => $this->interval_days,
            'is_active' => $this->is_active,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ? (float) $this->discount_value : null,
            'top_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->filter(fn ($badge) => ($badge->pivot->position ?? 'top') === 'top')->values()
            ),
            'bottom_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->filter(fn ($badge) => ($badge->pivot->position ?? '') === 'bottom')->values()
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
