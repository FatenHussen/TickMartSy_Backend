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
        $defaultAddress = $this->addresses?->firstWhere('is_default', true)
            ?? $this->addresses?->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'area' => $this?->area?->name,
            'address' => $defaultAddress?->full_address,
            'is_active' => (bool) $this->is_active,

            // Affiliate / Marketer info
            'affiliate' => [
                'is_affiliate' => (bool) $this->is_affiliate,
                'affiliate_approved'     => (bool) $this->affiliate_approved,
                'affiliate_id' => $this->affiliate_approved ? $this->affiliate_id : null,
                'coupon_code' => ($this->affiliate_approved && $this->is_affiliate)
                    ? $this->marketerCoupon?->code
                    : null,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

        ];
    }
}
