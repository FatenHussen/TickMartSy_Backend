<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NavMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'type' => $this->type,
            'icon' => $this->icon_url,
            'order' => $this->order,
            'open_in_new_tab' => (bool) $this->open_in_new_tab,
            // Resolved destination the app should navigate to.
            'target' => $this->resolveTarget($locale),
        ];
    }

    private function resolveTarget(string $locale): array
    {
        return match ($this->type) {
            'page' => [
                'page_id' => $this->page_id,
                'slug' => $this->page?->slug,
            ],
            'category' => [
                'category_id' => $this->category_id,
                'name' => $this->category?->getTranslation('name', $locale),
            ],
            'brand' => [
                'brand_id' => $this->brand_id,
                'name' => $this->brand?->getTranslation('name', $locale),
            ],
            'url' => [
                'url' => $this->url,
            ],
            default => [
                'route_key' => $this->route_key,
            ],
        };
    }
}
