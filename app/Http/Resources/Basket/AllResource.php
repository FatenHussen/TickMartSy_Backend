<?php

namespace App\Http\Resources\Basket;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

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

        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        // For scheduled baskets, use default schedule's discount values
        $discountType = $this->discount_type;
        $discountValue = $this->discount;

        if ($this->is_schedule && $this->defaultSchedule) {
            $discountType = $this->defaultSchedule->discount_type;
            $discountValue = $this->defaultSchedule->discount_value;
        }

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'category'        =>  $this->category?->name,
            'image'          => $this->imageUrl ?? null,
            'num_varieties'   => $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d') ?? null,
            ...$this->withCurrency($this->calculated_price, 'original_price'),
            'discount_value'  => $discountValue,
            'discount_type'   => $discountType,
            ...$this->withCurrency($this->discount_amount, 'discount_amount'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            'rating'          => $this->average_rating,
            ...$this->withCurrency($this->discount_amount, 'saving'),
            'num_sold'        => (int) $this->num_sold,
            'is_on_offer'     => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'next_delivery_date' => $nextDelivery,
            ...$this->withCurrency($this->delivery_price ?? 0, 'delivery_price'),
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'default_schedule' => $defaultSchedule,
            'is_paused' => (bool) ($this->is_paused ?? false),
            'paused_at' => $this->paused_at?->format('Y-m-d H:i:s'),

            'top_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('pivot.position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('pivot.position', 'bottom')->values()
            ),

        ];
    }
}
