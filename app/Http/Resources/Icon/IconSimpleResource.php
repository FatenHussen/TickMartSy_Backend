<?php

namespace App\Http\Resources\Icon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IconSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image_url ?? null,
            'description' => $this->description,
        ];
    }
}
