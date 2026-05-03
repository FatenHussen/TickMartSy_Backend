<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuickActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'button_text' => $this->getTranslation('button_text', $locale),
            'page_id' => $this->page_id,
            'page_slug' => $this->page?->slug,
            'page_title' => $this->page?->title,
            'icon' => $this->icon_url,
            'order' => $this->order,
        ];
    }
}
