<?php

namespace App\Http\Resources\UserBasketSchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_run_date' => $this->next_run_date?->format('Y-m-d'),

            'schedule' => $this->whenLoaded('schedule', function () {
                return [
                    'id' => $this->schedule->id,
                    'name' => $this->schedule->name,
                    'interval_days' => $this->schedule->interval_days,
                    'discount_type' => $this->schedule->discount_type,
                    'discount_value' => round($this->schedule->discount_value, 2),
                    'is_active' => $this->schedule->is_active,
                ];
            }),

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => (int) $item->quantity,
                        'price' => $item->price,

                        'product' => $item->relationLoaded('product') && $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image' => $item->product->media->first()?->url,
                        ] : null,

                        'variant' => $item->relationLoaded('variant') && $item->variant ? [
                            'id' => $item->variant->id,
                            'name' => $item->variant->attributes_values->pluck('name')->toArray(),
                            'price' => $item->variant->price,
                        ] : null,
                    ];
                })->values();
            }),
        ];
    }
}
