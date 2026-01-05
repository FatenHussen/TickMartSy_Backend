<?php

namespace App\Http\Resources\SectionPage;

use App\Http\Resources\SectionItem\OneResource as SectionItemOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'name' => $this->name ?? $this->section->name,
            'type' => $this->section->type,
            'items' => $this->section->type === 'api'
                ? $this->api_data
                : SectionItemOneResource::collection($this->section->sectionItems),
            'see_more' => $this->section->see_more
                ? [
                    'type' => 'page',
                    'page' => ['slug' => $this->section->see_more_slug],
                    'params' => $this->section->filters
                ]
                : null,
        ];
    }
}
