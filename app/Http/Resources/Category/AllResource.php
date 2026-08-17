<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $childrenCount = $this->relationLoaded('activeChildren')
            ? $this->activeChildren->count()
            : $this->activeChildren()->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->image_url,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'is_restaurant' => (bool) $this->is_restaurant,
            'children_count' => $childrenCount,
            'has_children' => $childrenCount > 0,
            'children' => $this->whenLoaded('activeChildren', function () {
                return $this->activeChildren->map(fn ($child) => static::childCard($child));
            }),
        ];
    }

    public static function childCard($child): array
    {
        $childrenCount = $child->relationLoaded('activeChildren')
            ? $child->activeChildren->count()
            : $child->activeChildren()->count();

        return [
            'id' => $child->id,
            'name' => $child->name,
            'icon' => $child->image_url,
            'parent_id' => $child->parent_id,
            'order' => $child->order,
            'is_restaurant' => (bool) $child->is_restaurant,
            'children_count' => $childrenCount,
            'has_children' => $childrenCount > 0,
        ];
    }
}
