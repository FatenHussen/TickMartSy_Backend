<?php

namespace App\Http\Resources\Admin\NavMenuItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'type' => $this->type,
            'page_id' => $this->page_id,
            'page' => $this->page ? [
                'id' => $this->page->id,
                'title' => $this->page->title,
                'slug' => $this->page->slug,
            ] : null,
            'category_id' => $this->category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name ?? null,
            ] : null,
            'brand_id' => $this->brand_id,
            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name ?? null,
            ] : null,
            'url' => $this->url,
            'route_key' => $this->route_key,
            'icon' => $this->icon_url,
            'order' => $this->order,
            'is_active' => (bool) $this->is_active,
            'open_in_new_tab' => (bool) $this->open_in_new_tab,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
