<?php

namespace App\Http\Resources\Shop;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use App\Http\Resources\Vendor\AllResource as VendorAllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

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
            'id'                    => $this->id,
            'name'                  => $this->name,
            'description'            => $this->description,
            'logo_url'                => $this->logo_url,
            'is_active'             => $this->is_active,
            'average_rating'        => $this->average_rating,
            'ratings_count'         => $this->ratings_count,
            'is_open_now'           => $this->isOpenNow(),
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'is_favorite' => $this->is_favorite,

            'vendor' => VendorAllResource::make($this->vendor),
        ];
    }
}
