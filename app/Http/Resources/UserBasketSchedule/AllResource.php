<?php

namespace App\Http\Resources\UserBasketSchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalPrice = $this->items?->sum(fn($item) => $item->price * $item->quantity) ?? 0;

        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType  = $this->schedule?->discount_type ?? null;

        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountAmount = round($totalPrice * $discountValue / 100, 2);
            } else { 
                $discountAmount = round(min($discountValue, $totalPrice), 2);
            }
        }

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'category'        => $this->category?->name,
            'image'           => $this->category?->image_url ?? null,
            'num_varieties'   => $this->items?->count() ?? 0,
            'original_price'  => round($totalPrice, 2),
            'discount_value'  => $discountValue,
            'discount_type'   => $discountType,
            'discount_amount' => $discountAmount,
            'final_price'     => round($totalPrice - $discountAmount, 2),
            'next_run_date'   => $this->next_run_date?->format('Y-m-d'),
        ];
    }
}
