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
        $sold = $price - $priceAfterDiscount;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image_url,
            'video_title' => $this->video_title,
            'video_desc' => $this->video_desc,
            'rating' => $this->average_rating ?? 0,

            ...$this->withCurrency($price, 'price'),
            ...$this->withCurrency($priceAfterDiscount, 'price_after_discount'),
            ...$this->withCurrency($sold, 'sold'),

            'discount' => $this->discount,
            'orders_count' => $this->orders_count,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            // 'budges'                => OneResource::collection($this->badges),
            'is_favorite' => (bool) ($this->is_favorite ?? false),

            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'top')->values()
            ),
            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),
        ];
    }
}
