<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class AdminOneResource extends JsonResource
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
            'name'                  => $this->getTranslations('name'),
            'owner_name'            => $this->owner_name,
            'owner_phone'           => $this->owner_phone,

            'commercial_register'   => $this->commercial_register,
            'contract_date'         => $this->contract_date?->format('Y-m-d'),
            'contract_number'       => $this->contract_number,
            'contract_duration_months' => $this->contract_duration_months,
            'commission_rate'       => $this->commission_rate,

            'logo_url'                => $this->getLogoUrl(),
            // 'cover_images_urls'      => $this->getCoverImagesUrls(),

            'is_active'             => $this->is_active,
            'average_rating'        => $this->average_rating,
            'ratings_count'         => $this->ratings_count,

            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'            => $this->updated_at?->format('Y-m-d H:i'),
            'is_favorite' => (bool) ($this->is_favorite ?? false),


        ];
    }
}
