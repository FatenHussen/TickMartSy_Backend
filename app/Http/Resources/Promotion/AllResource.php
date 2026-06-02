<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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
            'description' => $this->description,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'page_slugs' => $this->whenLoaded('pages', fn () => $this->pages->pluck('slug')->values()->all()),
            'product_ids' => $this->whenLoaded('products', fn () => $this->products->pluck('id')->values()->all()),
            'category_ids' => $this->whenLoaded('categories', fn () => $this->categories->pluck('id')->values()->all()),
            'shop_ids' => $this->whenLoaded('shops', fn () => $this->shops->pluck('id')->values()->all()),
            'vendor_ids' => $this->whenLoaded('vendors', fn () => $this->vendors->pluck('id')->values()->all()),
        ];
    }
}
