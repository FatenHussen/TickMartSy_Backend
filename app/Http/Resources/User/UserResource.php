<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Address\OneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $response = [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,

                // phone OR email
                $this->phone ? 'phone' : 'email' => $this->phone ?? $this->email,

                'addresses' => OneResource::collection($this->addresses),
                'is_subscription' => $this->hasActiveSubscription(),

                // Affiliate / Marketer info
                'affiliate' => [
                    'is_affiliate' => (bool) $this->is_affiliate,
                    'approved'     => (bool) $this->affiliate_approved,
                    'affiliate_id' => $this->affiliate_approved ? $this->affiliate_id : null,
                    'coupon_id'    => $this->affiliate_approved ? $this->coupon_id : null,
                    'rate'         => $this->affiliate_approved ? $this->affiliate_rate : null,
                ],
                'currency' => $this->currency ? [
                    'id' => $this->currency->id,
                    'code' => $this->currency->code,
                    'symbol' => $this->currency->symbol,
                ] : null,
            ],
        ];

        $response['token'] = $this->createToken('AUTH')->plainTextToken;

        return $response;
    }
}
