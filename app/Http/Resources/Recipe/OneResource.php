<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
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
            'description' => $this->description,

            'image' => $this->image,
            'video_url' => $this->video_url,

            'rating' => $this->average_rating,
            'orders_count' => $this->orders_count,

            'discount' => $this->discount,
            'serves' => $this->serves,
            'prepare_time' => $this->prepare_time,
            'budges'                => BadgeOneResource::collection($this->badges),

            'totals' => $this->whenLoaded('items', function () {
                $total_before_discount = $this->items->sum(function ($item) {
                    return $item->shopProductVariant->price * $item->quantity;
                });

                $discount_percentage = $this->discount ?? 0;
                $total_after_discount = $total_before_discount * (1 - $discount_percentage / 100);
                $discount_value = $total_before_discount - $total_after_discount;

                return [
                    'total_before_discount' => round($total_before_discount, 2),
                    'total_after_discount' => round($total_after_discount, 2),
                    'discount_value' => round($discount_value, 2),
                ];
            }),
            'steps' => RecipeStepResource::collection(
                $this->whenLoaded('steps')
            ),

            'items' => RecipeItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at,
        ];
    }
}
