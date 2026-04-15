<?php

namespace App\Http\Resources\Shop;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $coverImages = $this->getCoverImagesUrls();

        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'address'               => $this->address,
            'phone'                 => $this->phone,
            'mobile'                => $this->mobile,
            'email'                 => $this->email,
            'working_hours'         => $this->working_hours,
            'logo_url'              => $this->logo_url,
            'cover_image'           => $coverImages[0] ?? null,
            'cover_images_urls'      => $this->getCoverImagesUrls(),
            'is_active'             => $this->is_active,
            'average_rating'        => $this->average_rating,
            'ratings_count'         => $this->ratings_count,
            'is_open_now'           => $this->isOpenNow(),
            'is_service_provider'   => (bool) $this->is_service_provider,
            'is_restaurant'         => (bool) $this->is_restaurant,
            'payment_methods'       => $this->payment_methods ?? [],
            'pricing_tier'          => $this->pricing_tier?? null ,
            'is_recommended'        => (bool) $this->is_recommended,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'badges'                => BadgeOneResource::collection($this->badges),

            'area'                 => $this->area->name,
            'categories'           => $this->getShopCategories(),
            'services'              => $this->whenLoaded('services', fn() => $this->services),
            'coupons'               => $this->whenLoaded('coupons', fn() => $this->coupons->map(function ($coupon) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'is_active' => (bool) $coupon->is_active,
                ];
            })->values()),

            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'            => $this->updated_at?->format('Y-m-d H:i')
        ];
    }
}
