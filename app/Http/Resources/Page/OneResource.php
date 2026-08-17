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
            'sections' => AdminOneResource::collection(
                $this->whenLoaded('pageSections')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
