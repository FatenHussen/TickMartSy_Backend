<?php

namespace App\Http\Resources\Admin\VendorService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'vendor_service_type_id' => $this->vendor_service_type_id,
            'name' => $this->getTranslation('name', $locale),
            'description' => $this->description ? $this->getTranslation('description', $locale) : null,
            'type' => $this->type ? [
                'id' => $this->type->id,
                'name' => $this->type->getTranslation('name', $locale),
                'is_active' => (bool) $this->type->is_active,
            ] : null,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
