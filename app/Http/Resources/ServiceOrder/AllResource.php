<?php

namespace App\Http\Resources\ServiceOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'shop' => $this->shop ? [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
                'lat' => $this->shop->lat,
                'lng' => $this->shop->lng,
            ] : null,
            'vendor_service' => $this->vendorService ? [
                'id' => $this->vendorService->id,
                'name' => $this->vendorService->name,
            ] : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'notes' => $this->notes,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ] : null,
        ];
    }
}
