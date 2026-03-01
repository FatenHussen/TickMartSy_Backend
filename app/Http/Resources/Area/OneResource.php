<?php

namespace App\Http\Resources\Area;

use App\Http\Resources\City\AllResource as CityOneResource;
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
            'name' => $this->getTranslations('name'),
            'city' => CityOneResource::make($this->city),
            'base_fee' => $this->base_fee,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
