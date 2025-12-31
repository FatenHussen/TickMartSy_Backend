<?php

namespace App\Http\Resources\Admin\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', $locale),
            'description' => $this->getTranslation('description', $locale),
            'icon' => $this->icon,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function () use ($locale) {
                return [
                    'id' => $this->parent?->id,
                    'name' => $this->parent?->getTranslation('name', $locale),
                ];
            }),
            'children_count' => $this->children()->count(),
            // 'stores_count' => $this->stores()->count(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
