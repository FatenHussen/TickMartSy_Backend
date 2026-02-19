<?php

namespace App\Http\Resources\User;

use App\Http\Resources\PaymentMethod\PaymentMethodResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'name' => $this->name ?? 'User',
            'phone' => $this->phone ?? 'Phone',
            'email' => $this->email ?? 'Email',
            'image' => $this->image_url,
            'preferred_payment_method' => $this->whenLoaded('preferredPaymentMethod', function () {
                return new PaymentMethodResource($this->preferredPaymentMethod);
            }),
            'orders_count' => $this->orders_count ?? $this->orders()->count(),
            'baskets_count' => $this->baskets_count ?? $this->userBasketSchedules()->count(),
            'points' => $this->pointWallet?->balance ?? 0,
        ];
    }
}
