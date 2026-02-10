<?php

namespace App\Http\Resources\Basket;

use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;

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
            'image' => $this->imageUrl,
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'num_varieties'   => (int) $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d') ??null ,
            'original_price'    => round($this->calculated_price, 2),
            'discount_value'    => $this->discount,
            'discount_type'     => $this->discount_type,
            'discount_amount'   => round($this->discount_amount, 2),
            'final_price'       => round($this->final_price, 2),
            'rating'    => number_format((float) $this->rating, 1),
            'num_sold'  => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),

            'items' => $this->whenLoaded('items', function () {
                return BasketItemResource::collection($this->items->where('is_extra', 0));
            }),

            'extras' => $this->whenLoaded('items', function () {
                return BasketItemResource::collection($this->items->where('is_extra', 1));
            }) ?? [],
            
            'schedules' => $this->is_schedule
                ? BasketScheduleAllResource::collection($this->schedules)
                : [],

        ];
    }
}
