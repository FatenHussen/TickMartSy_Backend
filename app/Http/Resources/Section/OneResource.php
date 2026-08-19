<?php

namespace App\Http\Resources\Section;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SectionItem\OneResource as SectionItemOneResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'type' => $this->type,
            'content_type' => $this->contentType(),
            'variant' => $this->variant,
            'background_color' => $this->background_color,
            'background_card_color' => $this->background_card_color,
            'is_active' => (bool) $this->is_active,
            'pages_count' => $this->whenCounted('pages'),

            'api' => $this->type === 'api' ? [
                'api_method' => $this->api_method,
                'filters' => $this->filters,
                'see_more' => $this->see_more
                    ? ['page_slug' => $this->see_more_slug]
                    : null,
                'action' => ['page_slug' => $this->details_slug],
            ] : null,

            'manual' => $this->type === 'manual' ? [
                'manual_model' => $this->manual_model,
                'content_type' => $this->contentType(),
            ] : null,

            'items' => $this->type === 'api'
                ? SectionApiItemResource::collection($this->apiData() ?? collect())
                : SectionItemOneResource::collection($this->whenLoaded('sectionItems')),
        ];
    }
}
