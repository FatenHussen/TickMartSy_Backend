<?php

namespace App\Http\Resources\Address;

use App\Http\Resources\Area\OneResource as AreaOneResource;
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
            'area' => AreaOneResource::make($this->area),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
