<?php

namespace App\Http\Resources\Basket;

use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

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
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;
        $nextDelivery = null;
        $defaultSchedule = null;

        // Get default schedule info for scheduled baskets
        if ($this->is_schedule && $this->defaultSchedule) {
            $defaultSchedule = [
                'id' => $this->defaultSchedule->id,
                'title' => $this->defaultSchedule->title,
                'number_of_days' => $this->defaultSchedule->number_of_days,
                'discount_type' => $this->defaultSchedule->discount_type,
                'discount_value' => $this->defaultSchedule->discount_value,
            ];

            // Calculate next delivery based on default schedule
            $nextDelivery = now()->addDays($this->defaultSchedule->number_of_days)->format('Y-m-d');
        }

        // Get default schedule info for scheduled baskets
        $defaultSchedule = null;
        if ($this->is_schedule && $this->defaultSchedule) {
            $defaultSchedule = [
                'id' => $this->defaultSchedule->id,
                'title' => $this->defaultSchedule->title,
                'number_of_days' => $this->defaultSchedule->number_of_days,
                'discount_type' => $this->defaultSchedule->discount_type,
                'discount_value' => $this->defaultSchedule->discount_value,
            ];
        }

        // For scheduled baskets, use default schedule's discount values
        $discountType = $this->discount_type;
        $discountValue = $this->discount;

        if ($this->is_schedule && $this->defaultSchedule) {
            $discountType = $this->defaultSchedule->discount_type;
            $discountValue = $this->defaultSchedule->discount_value;
        }

        $categoryNames = ($this->categories ?? collect())->pluck('name')->filter()->implode(' - ');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->imageUrl,
            'category' => $categoryNames !== '' ? $categoryNames : $this->category?->name,
            'num_varieties'   => (int) $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d') ?? null,
            ...$this->withCurrency($this->calculated_price, 'original_price'),
            'discount_value'    => $discountValue,
            'discount_type'     => $discountType,
            ...$this->withCurrency($this->discount_amount, 'discount_amount'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            'rating'    => $this->average_rating,
            'num_sold'  => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'is_schedule' => $this->is_schedule,

            'items' => $this->whenLoaded('items', function () {
                $items = $this->items ?? collect();
                return BasketItemResource::collection($items->where('is_extra', 0));
            }),

            'extras' => $this->whenLoaded('items', function () {
                $items = $this->items ?? collect();
                return BasketItemResource::collection($items->where('is_extra', 1));
            }) ?? [],

            'schedules' => $this->is_schedule
                ? BasketScheduleAllResource::collection($this->schedules ?? collect())
                : [],
            'default_schedule' => $defaultSchedule,
            'is_favorite' => (bool) ($this->is_favorite ?? false),

            'top_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('position', 'top')->values()
            ),
            'next_delivery_date' => $nextDelivery,

            'bottom_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('position', 'bottom')->values()
            ),


        ];
    }
}
