<?php

namespace App\Http\Resources\Admin\QuickAction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'button_text' => $this->getTranslations('button_text'),
            'page_id' => $this->page_id,
            'page' => [
                'id' => $this->page?->id,
                'title' => $this->page?->title,
                'slug' => $this->page?->slug,
            ],
            'icon' => $this->icon_url,
            'order' => $this->order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}


