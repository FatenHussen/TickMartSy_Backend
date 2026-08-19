<?php

namespace App\Http\Resources\Admin\NavMenuItem;

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
            'type' => $this->type,
            'page_id' => $this->page_id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'url' => $this->url,
            'route_key' => $this->route_key,
            'icon' => $this->icon_url,
            'order' => $this->order,
            'is_active' => (bool) $this->is_active,
            'open_in_new_tab' => (bool) $this->open_in_new_tab,
        ];
    }
}
