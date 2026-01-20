<?php

namespace App\Http\Resources\Brand;

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
            'name' => $this->name,
            'image' => $this->image_url,
            'products_count'  => $this->products->count() ?? 0,
            'stores_count'    => $this->vendors->count() ?? 0,
            'rating' => $this->rating ?? 0
        ];
    }
}
