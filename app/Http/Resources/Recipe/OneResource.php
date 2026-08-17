<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    use HasCurrencyConversion;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = $this->getTotalItemsPrice();
        $priceAfterDiscount = $this->getTotalAfterDiscount();

        return [
            'id' => $this->id,

            'name' => $this->name,
            'description' => $this->description,

            'image' => $this->image_url,
            'images' => $this->image_urls,
            'video_url' => $this->video_url,
            'video_title' => $this->video_title,
            'video_desc' => $this->video_desc,

            'rating' => $this->average_rating ?? 0,
            'orders_count' => $this->orders_count,
            'is_active' => (bool) $this->is_active,

            ...$this->withCurrency($price, 'price'),
            ...$this->withCurrency($priceAfterDiscount, 'price_after_discount'),
            'discount' => $this->discount,
            'serves' => $this->serves,
            'prepare_time' => $this->prepare_time,
            // 'budges'                => BadgeOneResource::collection($this->badges),
            'is_favorite' => (bool) ($this->is_favorite ?? false),

            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),

            'totals' => $this->whenLoaded('items', function () {
                $total_before_discount = $this->items->sum(function ($item) {
                    return (float) $item->shopProductVariant?->productVariant?->price * $item->quantity;
                });

                $discount_percentage = $this->discount ?? 0;
                $total_after_discount = $total_before_discount * (1 - $discount_percentage / 100);
                $discount_value = $total_before_discount - $total_after_discount;

                return array_merge(
                    $this->withCurrency($total_before_discount, 'total_before_discount'),
                    $this->withCurrency($total_after_discount, 'total_after_discount'),
                    $this->withCurrency($discount_value, 'discount_value')
                );
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
