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
            'description' => $this->getTranslations('description'),
            'icon' => $this->image_url,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', function () use ($locale) {
                return [
                    'id' => $this->parent?->id,
                    'name' => $this->parent?->getTranslation('name', $locale),
                ];
            }),
            'children' => $this->whenLoaded('children', function () use ($locale) {
                return $this->children->map(function ($child) use ($locale) {
                    return [
                        'id' => $child->id,
                        'name' => $child->getTranslation('name', $locale),
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
