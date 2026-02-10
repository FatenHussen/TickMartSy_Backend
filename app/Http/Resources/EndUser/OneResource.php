<?php

namespace App\Http\Resources\EndUser;

use App\Http\Resources\Address\OneResource as AddressOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $response = [
            'id' => $this->id,
            'name' => $this->name,

            // phone OR email
            $this->phone ? 'phone' : 'email' => $this->phone ?? $this->email,

            'addresses' => AddressOneResource::collection($this->addresses),

            // Affiliate / Marketer info
            'affiliate' => [
                'is_affiliate' => (bool) $this->is_affiliate,
                'affiliate_approved'     => (bool) $this->affiliate_approved,
                'affiliate_id' => $this->affiliate_approved ? $this->affiliate_id : null,
                'coupon_id'    => $this->affiliate_approved ? $this->coupon_id : null,
                'affiliate_rate'         => $this->affiliate_approved ? $this->affiliate_rate : null,
            ],

        ];

        return $response;
    }
}
