<?php

namespace App\Http\Resources\Admin\ProductExtraDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->getTranslation('name', $locale),
            ] : null,
            'detail_key' => $this->getTranslation('detail_key', $locale),
            'detail_value' => $this->getTranslation('detail_value', $locale),
            'is_active' => (bool) $this->is_active,
        ];
    }
}
