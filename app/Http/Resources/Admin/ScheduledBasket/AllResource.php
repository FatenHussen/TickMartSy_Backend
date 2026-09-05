<?php

namespace App\Http\Resources\Admin\ScheduledBasket;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_ids' => ($this->categories ?? collect())->pluck('id')->values(),
            'categories' => ($this->categories ?? collect())->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            })->values(),
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image_url ?? null,
            'images' => $this->image_urls ?? [],
            'num_varieties' => (int) $this->num_varieties,

            'original_price' => round($this->calculated_price, 2),
            'discount' => $this->resolvedDiscountValue(),
            'discount_type' => $this->resolvedDiscountType(),
            'has_custom_discount' => (bool) $this->has_custom_discount,
            'discount_amount' => round($this->discount_amount, 2),
            'final_price' => round($this->final_price, 2),

            'rating' => (float) $this->rating,
            'average_rating' => $this->average_rating,
            'num_sold' => (int) $this->num_sold,

            'delivery_price' => (float) $this->delivery_price,
            'is_schedule' => true,
            'schedule_id' => $this->schedule_id,
            'schedule' => $this->when(
                $this->relationLoaded('catalogSchedule') && $this->catalogSchedule,
                fn () => [
                    'id' => $this->catalogSchedule->id,
                    'name' => $this->catalogSchedule->name,
                    'interval_days' => (int) $this->catalogSchedule->interval_days,
                    'discount_type' => $this->catalogSchedule->discount_type,
                    'discount_value' => $this->catalogSchedule->discount_value !== null
                        ? (float) $this->catalogSchedule->discount_value
                        : 0,
                    'is_active' => (bool) $this->catalogSchedule->is_active,
                ]
            ),

            'has_schedule' => $this->schedules->isNotEmpty(),
            'schedule_count' => $this->schedules->count(),
            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'top_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('position', 'bottom')->values()
            ),
        ];
    }
}
