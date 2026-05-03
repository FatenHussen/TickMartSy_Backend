<?php

namespace App\Http\Resources\Admin\QuickAction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'button_text' => $this->getTranslation('button_text', $locale),
            'page' => [
                'id' => $this->page?->id,
                'title' => $this->page?->title,
                'slug' => $this->page?->slug,
            ],
            'icon' => $this->icon_url,
            'order' => $this->order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
