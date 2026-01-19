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
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image_url,
            'categories_count' => $this->categories->count(),
            'products_count'  => $this->products->count(),
            'stores_count'    => $this->vendors->count(),
        ];
    }
}
