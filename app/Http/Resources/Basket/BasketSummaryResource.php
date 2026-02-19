<?php

namespace App\Http\Resources\Basket;

use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->imageUrl,
            'basket_type' => $this->basket_type ?? ($this->is_schedule ? 'subscription' : 'custom'),
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'num_varieties'   => (int) $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d') ?? null,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'original_price'    => round($this->calculated_price, 2),
            'discount_value'    => $this->discount,
            'discount_type'     => $this->discount_type,
            'discount_amount'   => round($this->discount_amount, 2),
            'final_price'       => round($this->final_price, 2),
            'rating'    => number_format((float) $this->rating, 1),
            'num_sold'  => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'schedules' => $this->is_schedule
                ? ($this->selected_schedule
                    ? [[ // Return only selected schedule for subscription baskets
                        'id' => $this->selected_schedule->id,
                        'title' => $this->selected_schedule->title,
                        'discount_type' => $this->selected_schedule->discount_type,
                        'discount_value' => (float) $this->selected_schedule->discount_value,
                        'number_of_days' => $this->selected_schedule->number_of_days,
                    ]]
                    : BasketScheduleAllResource::collection($this->schedules)) // Return all schedules if no selection
                : [],
        ];
    }
}
