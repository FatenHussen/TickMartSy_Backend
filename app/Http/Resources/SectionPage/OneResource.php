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
            'display_type_id' => $this->display_type_id,
            'position' => $this->position,
            'order' => $this->order,
            'type' => $this->section->type,
            'api' => [
                'api_source' =>  $this->section->api_source,
                'filters' => $this->filters,
            ],
            'manual' => [
                'section_items' => SectionItemOneResource::collection($this->section->sectionItems)
            ]
        ];
    }
}
