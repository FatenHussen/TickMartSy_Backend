<?php

namespace App\Http\Resources\Promotion;

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
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'page_slugs' => $this->whenLoaded('pages', fn () => $this->pages->pluck('slug')->values()->all()),
        ];
    }
}
