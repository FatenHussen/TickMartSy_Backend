<?php

namespace App\Http\Resources\Admin\Basket;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'image' => $this->image_url ?? null,
            'num_varieties' => (int) $this->num_varieties,
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),
            ...$this->withCurrency($this->calculated_price, 'original_price'),
            'discount_value' => $this->discount,
            'discount_type' => $this->discount_type,
            ...$this->withCurrency($this->discount_amount, 'discount_amount'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            'rating' => (float) $this->rating,
            'average_rating' => $this->average_rating,
            'num_sold' => (int) $this->num_sold,
            'is_on_offer' => $this->offer_ends_at && $this->offer_ends_at->isFuture(),
            ...$this->withCurrency($this->delivery_price, 'delivery_price'),
            'items_count' => $this->items()->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'is_schedule' => $this->is_schedule,
            'next_delivery_date' => $this->next_delivery_date,
            'is_active' => $this->is_active,
        ];
    }
}
