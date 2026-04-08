<?php

namespace App\Http\Resources\Admin\ShopVendorService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'vendor_service_id' => $this->vendor_service_id,
            'shop' => $this->shop ? [
                'id' => $this->shop->id,
                'name' => $this->shop->getTranslation('name', $locale),
                'is_active' => (bool) $this->shop->is_active,
            ] : null,
            'vendor_service' => $this->vendorService ? [
                'id' => $this->vendorService->id,
                'name' => $this->vendorService->getTranslation('name', $locale),
                'type' => $this->vendorService->type ? [
                    'id' => $this->vendorService->type->id,
                    'name' => $this->vendorService->type->getTranslation('name', $locale),
                ] : null,
            ] : null,
            'extra_details' => $this->extra_details,
            'price' => $this->price !== null ? (float) $this->price : null,
            'price_unit' => $this->price_unit,
            'duration_minutes' => $this->duration_minutes,
            'schedule' => $this->schedule,
            'is_open_now' => $this->isOpenNow(),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
