<?php

namespace App\Http\Resources\Admin\Basket;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'image' => $this->image_url ?? null,
            'num_varieties' => (int) $this->num_varieties,
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),
            'original_price' => round($this->calculated_price, 2),
            'discount_value' => $this->discount,
            'discount_type' => $this->discount_type,
            'discount_amount' => round($this->discount_amount, 2),
            'final_price' => round($this->final_price, 2),
            'rating' => (float) $this->rating,
            'average_rating' => $this->average_rating,
            'num_sold' => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            'delivery_price' => (float) $this->delivery_price,
            'items_count' => $this->items()->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'is_schedule' => $this->is_schedule,
            'next_delivery_date' => $this->next_delivery_date
        ];
    }
}
