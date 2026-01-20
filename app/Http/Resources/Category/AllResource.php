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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'parent' => $this->whenLoaded('parent', function (){
                return [
                    'id' => $this->parent?->id,
                    'name' => $this->parent?->name,
                ];
            }),
            'children' => $this->whenLoaded('children', function () {
                return $this->children->map(function ($child)  {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                    ];
                });
            }),
        ];
    }
}
