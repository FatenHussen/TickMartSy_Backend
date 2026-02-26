<?php

namespace App\Http\Resources\VendorUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'vendor_id' => $this->vendor_id,
            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
            ],
            'shops' => $this->shops?->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'email' => $shop->email,
                    'is_active' => $shop->is_active,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
