<?php

namespace App\Http\Resources\UserBasketSchedule;

use App\Http\Resources\ScheduledBasketAlertResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray(Request $request): array
    {
        $totalPrice = $this->items?->sum(fn($item) => $item->price * $item->quantity) ?? 0;

        $discountValue = $this->schedule?->discount_value ?? 0;
        $discountType = $this->schedule?->discount_type ?? null;

        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountAmount = round($totalPrice * $discountValue / 100, 2);
            } else {
                $discountAmount = round(min($discountValue, $totalPrice), 2);
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->category?->image_url ?? null,
            'num_varieties' => $this->items?->count() ?? 0,
            'is_paused' => $this->isPaused(),
            ...$this->withCurrency($totalPrice, 'original_price'),
            'discount_value' => $discountValue,
            'discount_type' => $discountType,
            ...$this->withCurrency($discountAmount, 'discount_amount'),
            ...$this->withCurrency($totalPrice - $discountAmount, 'final_price'),
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
        ];
    }
}
