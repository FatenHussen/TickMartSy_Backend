<?php

namespace App\Http\Resources\EndUser;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'area_id' => $this->area_id,

            // Affiliate / Marketer info
            'affiliate' => [
                'is_affiliate' => (bool) $this->is_affiliate,
                'affiliate_approved'     => (bool) $this->affiliate_approved,
                'affiliate_id' => $this->affiliate_approved ? $this->affiliate_id : null,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

        ];
    }
}
