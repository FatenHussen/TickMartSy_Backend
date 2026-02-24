<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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
            'rating' => $this->average_rating,
            ...$this->withCurrency($price, 'price'),
            ...$this->withCurrency($priceAfterDiscount, 'price_after_discount'),
            'discount' => $this->discount,
            'orders_count' => $this->orders_count,
            'created_at' => $this->created_at,
            // 'budges'                => OneResource::collection($this->badges),
            'is_favorite' => (bool) ($this->is_favorite ?? false),

            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'top')->values()
            ),
            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('pivot.position', 'bottom')->values()
            ),
        ];
    }
}
