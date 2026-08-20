<?php

namespace App\Http\Resources\Section;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'type' => $this->type,
            'content_type' => $this->contentType(),
            'manual_model' => $this->manual_model,
            'api_method' => $this->api_method,
            'filters' => $this->filters,
            'layout' => $this->layout,
            'variant' => $this->variant,
            'background_color' => $this->background_color,
            'background_card_color' => $this->background_card_color,
            'is_active' => (bool) $this->is_active,
            'pages_count' => $this->whenCounted('pages'),
        ];
    }
}
