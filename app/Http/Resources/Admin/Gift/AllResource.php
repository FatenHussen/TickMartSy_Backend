<?php

namespace App\Http\Resources\Admin\Gift;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name, // Will return translated based on locale
            'description' => $this->description,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'points_required' => $this->points_required,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => $this->is_active,
            'is_available' => $this->isAvailable(),
            'total_exchanges' => $this->exchanges_count,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
