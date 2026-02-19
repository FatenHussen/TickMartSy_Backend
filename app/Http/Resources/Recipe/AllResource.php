<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
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
            'rating' => $this->average_rating,
            'price' => $this->getTotalItemsPrice(),
            'price_after_discount' => $this->getTotalAfterDiscount(),
            'discount' => $this->discount,
            'orders_count' => $this->orders_count,
            'created_at' => $this->created_at,
            // 'budges'                => OneResource::collection($this->badges),
            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'top')->values()
            ),
            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'bottom')->values()
            ),
        ];
    }
}
