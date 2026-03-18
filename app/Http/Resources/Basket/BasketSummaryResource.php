<?php

namespace App\Http\Resources\Basket;

use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketSummaryResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray(Request $request): array
    {
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

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
            ...$this->withCurrency($this->calculated_price, 'original_price'),
            'discount_value'    => $this->discount,
            'discount_type'     => $this->discount_type,
            ...$this->withCurrency($this->discount_amount, 'discount_amount'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            'rating'    => number_format((float) $this->rating, 1),
            'num_sold'  => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'is_paused' => (bool) ($this->pause_at !== null),
            'paused_at' => $this->pause_at?->format('Y-m-d H:i:s') ?? null,
            'schedules' => $this->is_schedule
                ? ($this->selected_schedule
                    ? [[ // Return only selected schedule for subscription baskets
                        'id' => $this->selected_schedule->id,
                        'title' => $this->selected_schedule->title,
                        'discount_type' => $this->selected_schedule->discount_type,
                        'discount_value' => (float) $this->selected_schedule->discount_value,
                        'number_of_days' => $this->selected_schedule->number_of_days,
                    ]]
                    : BasketScheduleAllResource::collection($this->schedules ?? collect())) // Return all schedules if no selection
                : [],
        ];
    }
}
