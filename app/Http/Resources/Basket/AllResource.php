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

        if ($this->activeSchedule && $this->activeSchedule->count()) {
            $schedule = $this->activeSchedule->first();
            $nextDelivery = now()->addDays($schedule->number_of_days)->format('Y-m-d');
        }

        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'category'        =>  $this->category?->name,
            'image'          => $this->imageUrl ?? null,
            'num_varieties'   => $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d') ?? null,
            ...$this->withCurrency($this->calculated_price, 'original_price'),
            'discount_value'  => $this->discount,
            'discount_type'   => $this->discount_type,
            ...$this->withCurrency($this->discount_amount, 'discount_amount'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            'rating'          => $this->average_rating,
            ...$this->withCurrency($this->discount_amount, 'saving'),
            'num_sold'        => (int) $this->num_sold,
            'is_on_offer'     => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'next_delivery_date' => $nextDelivery,
            ...$this->withCurrency($this->delivery_price ?? 0, 'delivery_price'),
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
