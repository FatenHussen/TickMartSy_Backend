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
        $childrenCount = $this->relationLoaded('activeChildren')
            ? $this->activeChildren->count()
            : $this->activeChildren()->count();

        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'icon' => $this->image_url,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'is_restaurant' => (bool) $this->is_restaurant,
            'children_count' => $childrenCount,
            'has_children' => $childrenCount > 0,
            'parent' => $this->whenLoaded('parent', function () use ($locale) {
                return [
                    'id' => $this->parent?->id,
                    'name' => $this->parent?->getTranslation('name', $locale),
                ];
            }),
            'children' => $this->whenLoaded('activeChildren', function () {
                return $this->activeChildren->map(fn ($child) => AllResource::childCard($child));
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
