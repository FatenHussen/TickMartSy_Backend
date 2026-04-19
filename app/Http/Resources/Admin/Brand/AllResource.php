<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'image'      => $this->image_url,
            'order'      => $this->order,
            'is_active'  => $this->is_active,
            'governorate' => $this->governorate ? ['id' => $this->governorate->id, 'name' => $this->governorate->name] : null,
            'city'        => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null,
            'category'    => $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null,
            'origin_country' => $this->originCountry ? ['id' => $this->originCountry->id, 'name' => $this->originCountry->name] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
