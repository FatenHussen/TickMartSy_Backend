<?php

namespace App\Http\Resources\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'filters' => $this->filters,
            'is_category_page' => $this->category_id !== null,
            'category_id' => $this->category_id,
            'can_delete_page' => $this->category_id === null,
            'can_edit_metadata' => $this->category_id === null,
            'sections_count' => $this->whenCounted('pageSections'),
            'created_at' => $this->created_at,
        ];
    }
}
