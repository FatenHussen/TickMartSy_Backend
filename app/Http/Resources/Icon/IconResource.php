<?php

namespace App\Http\Resources\Icon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IconResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image_url ?? null,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
