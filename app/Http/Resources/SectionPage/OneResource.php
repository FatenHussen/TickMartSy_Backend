<?php

namespace App\Http\Resources\SectionPage;

use App\Http\Resources\Section\SectionApiItemResource;
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
            'position' => $this->position,
            'order' => $this->order,
            'see_more' => $this->section->see_more
                ? [
                    'page_slug' => $this->section->see_more_slug,
                    'params' => $this->filters
                ]
                : null,

            'action' => [
                'page_slug' => $this->section->details_slug,

            ],
            'items' => $this->section->type === 'api'
                ? SectionApiItemResource::collection($this->api_data)
                : SectionItemOneResource::collection($this->section->sectionItems),
        ];
    }
}
