<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOneResource extends JsonResource
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


            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),

            'image' => $this->image_url,
            'images' => $this->image_urls,
            'video_url' => $this->video_url,
            'video_title' => $this->getTranslations('video_title'),
            'video_desc' => $this->getTranslations('video_desc'),

            'rating' => $this->average_rating ?? 0,
            'orders_count' => $this->orders_count,
            'is_active' => (bool) $this->is_active,

            'discount' => $this->discount,
            ...$this->withCurrency($price, 'price'),
            ...$this->withCurrency($priceAfterDiscount, 'price_after_discount'),

            'delivery_price' => $this->delivery_price,
            'serves' => $this->serves,
            'prepare_time' => $this->prepare_time,

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
            'steps' => AdminRecipeStepResource::collection(
                $this->whenLoaded('steps')
            ),

            'items' => RecipeItemResource::collection(
                $this->whenLoaded('items')
            ),

            'badges' => BadgeOneResource::collection(
                $this->badges
            ),
            'created_at' => $this->created_at,
        ];
    }
}
