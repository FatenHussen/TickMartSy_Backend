<?php

namespace App\Http\Resources\Promotion;

use App\Http\Resources\Admin\ShopVendorService\AllResource as ShopVendorServiceAllResource;
use App\Http\Resources\Category\AllResource as CategoryAllResource;
use App\Http\Resources\Product\AllResource as ProductAllResource;
use App\Http\Resources\Shop\AllResource as ShopAllResource;
use App\Http\Resources\Vendor\AllResource as VendorAllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOneResource extends JsonResource
{
    /**
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
            'page_slugs' => $this->whenLoaded('pages', fn() => $this->pages->pluck('slug')->values()->all()),
            'products' => ProductAllResource::collection($this->whenLoaded('products')),
            'categories' => CategoryAllResource::collection($this->whenLoaded('categories')),
            'shops' => ShopAllResource::collection($this->whenLoaded('stores')),
            'restaurants' => ShopAllResource::collection($this->whenLoaded('restaurants')),
            'serviceProviders' => ShopAllResource::collection($this->whenLoaded('serviceProviders')),
            // 'vendors' => VendorAllResource::collection($this->whenLoaded('vendors')),
            'shop_vendor_services' => ShopVendorServiceAllResource::collection($this->whenLoaded('shopVendorServices')),
        ];
    }
}
