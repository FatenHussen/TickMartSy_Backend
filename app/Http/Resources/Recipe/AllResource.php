<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Governorate\OneResource as GovernorateOneResource;
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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image_url,
            'rating' => $this->rating,
            'discount' => $this->discount,
            'orders_count' => $this->orders_count,
            'created_at' => $this->created_at,
        ];
    }
}
