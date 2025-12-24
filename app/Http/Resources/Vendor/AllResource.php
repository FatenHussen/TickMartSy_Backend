<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\Area\OneResource as AreaOneResource;
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
        return[
           'id'                    => $this->id,
            'name'                  => $this->name,
            'owner_name'            => $this->owner_name,
            'logo_url'                => $this->getLogoUrl(),
            'is_active'             => $this->is_active,
            'average_rating'        => $this->average_rating,
            'ratings_count'         => $this->ratings_count,
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
