<?php

namespace App\Http\Resources\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
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
            'label' => $this->label,
            'street_name' => $this->street_name,
            'nearest_landmark' => $this->nearest_landmark,
            'building_number' => $this->building_number,
            'floor_apartment' => $this->floor_apartment,
            'contact_phone' => $this->contact_phone,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'is_default' => $this->is_default,
            // Locale string (same as Area\AllResource) — not {ar,en} translations.
            'area' => $this->when($this->area, fn () => [
                'id' => $this->area->id,
                'name' => $this->area->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
