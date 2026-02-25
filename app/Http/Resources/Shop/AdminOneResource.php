<?php

namespace App\Http\Resources\Shop;

// use App\Http\Resources\Area\OneResource as AreaOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use App\Http\Resources\Area\OneResource as AreaOneResource;

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
            'description'           => $this->getTranslations('description'),
            'address'               => $this->getTranslations('address'),
            'phone'                 => $this->phone,
            'mobile'                => $this->mobile,
            'email'                 => $this->email,
            'working_hours'         => $this->working_hours,
            // 'cover_images_urls'      => $this->getCoverImagesUrls(),
            'is_active'             => $this->is_active,
            'average_rating'        =>  $this->average_rating ?? 0,
            // 'ratings_count'         => $this->ratings_count,
            'is_open_now'           => $this->isOpenNow(),
            'logo_url'                => $this->logo_url,
            'area'                 =>  AreaOneResource::make($this->area),
            'services'              => $this->whenLoaded('services', fn() => $this->services),
            // 'categories'            => $this->whenLoaded('categories', fn() => $this->categories),
            'badges' => BadgeOneResource::collection(
                $this->badges
            ),
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'updated_at'            => $this->updated_at?->format('Y-m-d H:i')
        ];
    }
}
