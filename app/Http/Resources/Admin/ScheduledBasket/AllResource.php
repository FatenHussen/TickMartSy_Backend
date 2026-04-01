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
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'name' => $this->name,
            'image' => $this->image_url ?? null,
            'num_varieties' => (int) $this->num_varieties,

            // Pricing
            'original_price' => round($this->calculated_price, 2),
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'discount_amount' => round($this->discount_amount, 2),
            'final_price' => round($this->final_price, 2),

            // Stats
            'rating' => (float) $this->rating,
            'average_rating' => $this->average_rating,
            'num_sold' => (int) $this->num_sold,

            // Delivery
            'delivery_price' => (float) $this->delivery_price,
            'is_schedule' => true,

            // Schedule info
            'has_schedule' => $this->schedules->isNotEmpty(),
            'schedule_count' => $this->schedules->count(),
            'is_active' => $this->is_active,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
             'top_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('pivot.position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('pivot.position', 'bottom')->values()
            ),

        ];
    }
}
