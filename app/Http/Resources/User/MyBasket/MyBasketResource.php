<?php

namespace App\Http\Resources\User\MyBasket;

use App\Http\Resources\ScheduledBasketAlertResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyBasketResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray(Request $request): array
    {
        $items = $this->items ?? collect();

        $calculatedPrice = $items->sum(function ($item) {
            if (!$item || !$item->product) {
                return 0;
            }
            return $item->price * $item->quantity;
        });

        $discountAmount = \App\Support\ScheduleDiscount::amount(
            $calculatedPrice,
            $this->schedule?->discount_type,
            $this->schedule?->discount_value ?? 0,
        );

        $finalPrice = $calculatedPrice - $discountAmount;

        $firstItem = $items->first();
        $imageUrl = null;

        if ($firstItem && $firstItem->product) {
            $imageUrl = $firstItem->product->image_url ?? null;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $imageUrl,
            'basket_type' => $this->basket_type ?? 'user-schedule',
            'is_active' => $this->is_active,
            'is_paused' => (bool) ($this->paused_at ? true : false),
            'paused_at' => $this->paused_at?->format('Y-m-d H:i:s') ?? null,
            'num_varieties' => $items->count(),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),
            'availability' => $this->availability_summary ? [
                'status' => $this->availability_summary['status'],
                'has_issue' => $this->availability_summary['has_issue'],
                'unavailable_items_count' => $this->availability_summary['unavailable_items_count'],
                'warning_items_count' => $this->availability_summary['warning_items_count'],
            ] : null,
            'active_alert' => $this->active_alert
                ? ScheduledBasketAlertResource::make($this->active_alert)
                : null,
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
