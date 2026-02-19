<?php

namespace App\Http\Resources\Store;

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
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'owner_name'            => $this->owner_name,
            'owner_phone'           => $this->owner_phone,
            'description'           => $this->description,
            'address'               => $this->address,
            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'bottom')->values()
            ),

            // 'phone'                 => $this->phone,
            // 'mobile'                => $this->mobile,
            // 'email'                 => $this->email,
            // 'commercial_register'   => $this->commercial_register,
            // 'contract_date'         => $this->contract_date?->format('Y-m-d'),
            // 'contract_number'       => $this->contract_number,
            // 'contract_duration_months' => $this->contract_duration_months,
            // 'commission_rate'       => $this->commission_rate,
            // 'working_hours'         => $this->working_hours,
            // 'logo_url'              => $this->getLogoUrl(),
            // 'cover_images_urls'      => $this->getCoverImagesUrls(),
            // 'is_active'             => $this->is_active,
            // 'average_rating'        => $this->average_rating,
            // 'ratings_count'         => $this->ratings_count,
            // 'is_open_now'           => $this->isOpenNow(),

            // 'area_id'                 => $this->area_id,
            // 'services'              => $this->whenLoaded('services', fn() => $this->services),
            // 'categories'            => $this->whenLoaded('categories', fn() => $this->categories),

            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'            => $this->updated_at?->format('Y-m-d H:i')
        ];
    }
}
