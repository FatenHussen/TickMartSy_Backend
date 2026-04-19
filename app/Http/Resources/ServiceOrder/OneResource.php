<?php

namespace App\Http\Resources\ServiceOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'notes' => $this->notes,
            'date' => $this->date?->format('Y-m-d'),
            'time' => $this->resource->formattedOrderTime(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'shop' => $this->shop ? [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
                'lat' => $this->shop->lat,
                'lng' => $this->shop->lng,
            ] : null,
            'vendor_service' => $this->vendorService ? [
                'id' => $this->vendorService->id,
                'name' => $this->vendorService->name,
                'description' => $this->vendorService->description,
            ] : null,
            'shop_vendor_service' => $this->shopVendorService ? [
                'id' => $this->shopVendorService->id,
                'extra_details' => $this->shopVendorService->extra_details,
                'duration_minutes' => $this->shopVendorService->duration_minutes,
                'schedule' => $this->shopVendorService->schedule,
            ] : null,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ] : null,
        ];
    }
}
