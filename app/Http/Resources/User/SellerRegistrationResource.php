<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Country\OneResource as CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerRegistrationResource extends JsonResource
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
            'email' => $this->email,
            'seller_name' => $this->seller_name,
            'store_name' => $this->store_name,
            'address' => $this->address,
            'commercial_register_number' => $this->commercial_register_number,
            'commercial_register_image' => $this->commercial_register_image,
            'gender' => $this->gender,
            'country_id' => $this->country_id,
            'country' => $this->country ? new CountryResource($this->country) : null,
            'city_id' => $this->city_id,
            'governorate_id' => $this->governorate_id,

            'logo' => $this->logo,
            'status' => $this->status ?? 'pending',
            'registered_at' => $this->registered_at,
        ];
    }
}
