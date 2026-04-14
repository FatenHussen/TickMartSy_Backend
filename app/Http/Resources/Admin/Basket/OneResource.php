<?php

namespace App\Http\Resources\Admin\Basket;

use App\Http\Resources\Badge\OneResource as BadgeOneResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'name' => $this->getTranslations('name'),
            'image' => $this->image_url ?? null,
            'num_varieties' => (int) $this->num_varieties,
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),
            'is_active' => $this->is_active,

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
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),

            // Delivery
            'delivery_price' => (float) $this->delivery_price,
            'is_schedule' => (bool) $this->is_schedule,

            // Items
            'items' => BasketItemResource::collection($this->items ?? []),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'next_delivery_date' => $this->next_delivery_date,

             'top_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                ($this->badges ?? collect())->where('position', 'bottom')->values()
            ),

            'schedules' => \App\Http\Resources\Admin\Schedule\OneResource::collection(
                $this->whenLoaded('schedules', $this->schedules ?? collect())
            ),
        ];
    }
}
