<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'icon' => $this->image_url,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'parent' => $this->whenLoaded('parent', function () use ($locale) {
                return [
                    'id' => $this->parent?->id,
                    'name' => $this->parent?->getTranslation('name', $locale),
                ];
            }),
            'children' => $this->whenLoaded('activeChildren', function () use ($locale) {
                return $this->activeChildren->map(function ($child) use ($locale) {
                    return [
                        'id' => $child->id,
                        'name' => $child->getTranslation('name', $locale),
                        'order' => $child->order,
                        'is_active' => $child->is_active,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
