<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\Area\OneResource as AreaOneResource;
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
            'owner_name'            => $this->owner_name,
            'commission_type'       => $this->commission_type ?? 'percentage',
            'commission_rate'       => (float) ($this->commission_rate ?? 0),
            'fixed_commission'      => (float) ($this->fixed_commission ?? 0),
            'settlement_cycle'      => $this->settlement_cycle ?? 'monthly',
            'logo_url'                => $this->getLogoUrl(),
            'is_active'             => $this->is_active,
            'average_rating'        => $this->average_rating,
            'ratings_count'         => $this->ratings_count,
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'is_favorite' => (bool) ($this->is_favorite ?? false),

            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),

        ];
    }
}
