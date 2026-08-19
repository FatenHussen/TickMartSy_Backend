<?php

namespace App\Http\Resources\Page;

use App\Http\Resources\PageSection\AdminOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'delete_page_via' => $this->category_id
                ? 'DELETE /api/admin/categories/' . $this->category_id
                : null,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->getTranslations('name'),
            ]),
            'sections' => AdminOneResource::collection(
                $this->whenLoaded('pageSections')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
