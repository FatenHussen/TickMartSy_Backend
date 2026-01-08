<?php

namespace App\Http\Resources\Admin\Category\CategoryAttribute;

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
            'id'       => $this->id,
            'name'     => $this->name,
            'category' => $this->category->name,
            'type' => $this->type,
            
        ];
    }
}
