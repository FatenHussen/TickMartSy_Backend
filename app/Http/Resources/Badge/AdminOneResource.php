<?php

namespace App\Http\Resources\Badge;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOneResource extends JsonResource
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
            'color' => $this->color,
            'type' => $this->type,
            'image' => $this->image_url,
            'is_active' => (bool) $this->is_active,
            'position' => $this->position,
        ];
    }
}
