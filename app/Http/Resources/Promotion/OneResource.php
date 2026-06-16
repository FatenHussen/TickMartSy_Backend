<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'type' => $this->type,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'min_spend' => $this->min_spend,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,
            'gift_description' => $this->getTranslations('gift_description'),
            'reward_points' => $this->reward_points,
            'page_slugs' => $this->whenLoaded('pages', fn () => $this->pages->pluck('slug')->values()->all()),
            'product_ids' => $this->whenLoaded('products', fn () => $this->products->pluck('id')->values()->all()),
            'category_ids' => $this->whenLoaded('categories', fn () => $this->categories->pluck('id')->values()->all()),
            'shop_ids' => $this->whenLoaded('shops', fn () => $this->shops->pluck('id')->values()->all()),
            'vendor_ids' => $this->whenLoaded('vendors', fn () => $this->vendors->pluck('id')->values()->all()),
            'shop_vendor_service_ids' => $this->whenLoaded(
                'shopVendorServices',
                fn () => $this->shopVendorServices->pluck('id')->values()->all()
            ),
        ];
    }
}
