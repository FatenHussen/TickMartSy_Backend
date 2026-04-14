<?php

namespace App\Http\Resources\Coupon;

use App\Http\Resources\Category\AllResource as CategoryAllResource;
use App\Http\Resources\Product\AllResource;
use App\Http\Resources\Vendor\AllResource as VendorAllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'admin_name' =>    $this->getTranslations('name'),
            'affiliate_id' => $this->affiliate_id,
            'code' => $this->code,

            'discount' => [
                'type' => $this->discount_type,
                'value' => $this->discount_value,
            ],

            'start_at' => optional($this->start_at)->toDateTimeString(),
            'end_at'   => optional($this->end_at)->toDateTimeString(),

            'max_uses'   => $this->max_uses,
            'used_count' => $this->used_count,

            'is_active' => $this->is_active,

            // Helpers
            'is_expired' => $this->isExpired(),
            'is_valid'   => $this->isValid(),

            // Relations
            'governorate_id' => $this->governorate_id,
            'city_id' => $this->city_id,
            'governorate' => $this->governorate ? [
                'id' => $this->governorate->id,
                'name' => $this->governorate->name,
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ] : null,
            // 'user_id' =>    $this->user_id,

            'products' => AllResource::collection(
                $this->whenLoaded('products')
            ),

            'categories' => CategoryAllResource::collection(
                $this->whenLoaded('categories')
            ),

            'vendors' => VendorAllResource::collection(
                $this->whenLoaded('vendors')
            ),

            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
