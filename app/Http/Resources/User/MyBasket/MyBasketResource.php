<?php

namespace App\Http\Resources\User\MyBasket;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyBasketResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray(Request $request): array
    {
        $calculatedPrice = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $discountAmount = 0;
        if ($this->schedule && $this->schedule->discount_value > 0) {
            if ($this->schedule->discount_type === 'percent') {
                $discountAmount = $calculatedPrice * ($this->schedule->discount_value / 100);
            } else {
                $discountAmount = min($this->schedule->discount_value, $calculatedPrice);
            }
        }

        $finalPrice = $calculatedPrice - $discountAmount;

        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->items->first()?->product?->image_url,
            'basket_type' => $this->basket_type ?? 'user-schedule',
            'is_active' => $this->is_active,
            'is_paused' => (bool) ($this->is_paused ?? false),
            'paused_at' => $this->paused_at?->format('Y-m-d H:i:s') ?? null,
            'num_varieties' => $this->items->count(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('Y-m-d'),
            ...$this->withCurrency($calculatedPrice, 'original_price'),
            'discount_value' => $this->schedule?->discount_value ?? 0,
            'discount_type' => $this->schedule?->discount_type,
            ...$this->withCurrency($discountAmount, 'discount_amount'),
            ...$this->withCurrency($finalPrice, 'final_price'),
            'schedules' => $this->whenLoaded('schedule', function () {
                return [[
                    'id' => $this->schedule->id,
                    'title' => $this->schedule->name,
                    'discount_type' => $this->schedule->discount_type,
                    'discount_value' => (float) $this->schedule->discount_value,
                    'number_of_days' => $this->schedule->interval_days,
                ]];
            }) ?? [],
        ];
    }
}
