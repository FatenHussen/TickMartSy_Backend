<?php

namespace App\Http\Resources\Admin\UserBasketSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        // Calculate total price
        $totalPrice = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Calculate discount
        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType = $this->schedule?->discount_type ?? null;
        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType === 'percentage') {
                $discountAmount = round($totalPrice * $discountValue / 100, 2);
            } else {
                $discountAmount = round(min($discountValue, $totalPrice), 2);
            }
        }

        $finalPrice = round($totalPrice - $discountAmount, 2);

        return [
            'id' => $this->id,

            // User info
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],

            // Basket info
            'name' => $this->name,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],

            // Schedule info
            'schedule' => [
                'id' => $this->schedule?->id,
                'name' => $this->schedule?->name,
                'interval_days' => $this->schedule?->interval_days,
            ],

            // Status
            'is_active' => (bool) $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),

            // Pricing
            'items_count' => $this->items->count(),
            'total_price' => round($totalPrice, 2),
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
