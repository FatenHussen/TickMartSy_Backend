<?php

namespace App\Http\Resources\Section;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SectionItem\OneResource as SectionItemOneResource;

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
            'name' => $this->name,
            'type' => $this->type,
            'api' => [
                'api_method' => $this->api_method,
                'filters' => $this->filters,

                'see_more' => $this->see_more
                    ? [
                        'page_slug' => $this->see_more_slug,
                    ]
                    : null,

                'action' => [
                    'page_slug' => $this->details_slug,

                ],
            ],
            'manual' => [
                'manual_model' => $this->manual_model
            ],
            'items' => $this->type === 'api'
                ? SectionApiItemResource::collection($this->apiData())
                : SectionItemOneResource::collection($this->sectionItems),
        ];
    }
}
