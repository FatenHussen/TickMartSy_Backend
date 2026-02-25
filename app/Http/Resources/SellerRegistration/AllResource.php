<?php

namespace App\Http\Resources\SellerRegistration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seller_name' => $this->seller_name,
            'email' => $this->email,
            'store_name' => $this->store_name,
            'governorate' => $this->governorate?->name,
            'city' => $this->city?->name,
            'status' => $this->status,
            'registered_at' => $this->registered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
