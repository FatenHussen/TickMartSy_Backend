<?php

namespace App\Http\Resources\Admin\Gift;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' =>  $this->resource->getTranslations('name'),
            'description' =>  $this->resource->getTranslations('description'),
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'points_required' => $this->points_required,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => $this->is_active,
            'category_id' => $this->category_id,
            'terms_conditions' => $this->resource->getTranslations('terms_conditions'),
            'is_available' => $this->isAvailable(),
            'total_exchanges' => $this->exchanges_count,
            'pending_exchanges' => \App\Models\PointExchange::where('exchange_type', 'gift')
                ->whereRaw("JSON_EXTRACT(exchange_data, '$.gift_id') = ?", [$this->id])
                ->where('status', 'pending')
                ->count(),
            'completed_exchanges' => \App\Models\PointExchange::where('exchange_type', 'gift')
                ->whereRaw("JSON_EXTRACT(exchange_data, '$.gift_id') = ?", [$this->id])
                ->where('status', 'completed')
                ->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

