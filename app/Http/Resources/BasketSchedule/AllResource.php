<?php

namespace App\Http\Resources\BasketSchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'category'        =>  $this->category?->name,
            'image'          => $this->imageUrl ?? null,
            'num_varieties'   => $this->num_varieties,
            'offer_ends_at'   => $this->offer_ends_at?->format('Y-m-d'),
            'original_price'  => round($this->calculated_price, 2),
            'discount_value'  => $this->discount,
            'discount_type'   => $this->discount_type,
            'discount_amount' => round($this->discount_amount, 2),
            'final_price'     => round($this->final_price, 2),
            'rating'          => (float) $this->rating,
            'saving' => round($this->discount_amount,2),
            'num_sold'        => (int) $this->num_sold,
            'is_on_offer'     => $this->offer_ends_at && $this->offer_ends_at->isFuture(),

        ];
    }
}
