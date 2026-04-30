<?php

namespace App\Http\Resources\Admin\ProductExtraDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'detail_key' => $this->getTranslations('detail_key') ?? [],
            'detail_value' => $this->getTranslations('detail_value') ?? [],
            'is_active' => (bool) $this->is_active,
            'products_count' => $this->products_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
