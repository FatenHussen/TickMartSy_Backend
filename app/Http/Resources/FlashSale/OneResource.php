<?php

namespace App\Http\Resources\FlashSale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'product_count' => $this->products()->count(),
            'created_at' => $this->created_at,
        ];
    }
}
