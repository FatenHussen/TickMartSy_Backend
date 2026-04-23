<?php

namespace App\Http\Resources\Admin\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $localizedName = $this->getTranslation('name', app()->getLocale(), false)
            ?? $this->getTranslation('name', config('app.fallback_locale', 'en'), false)
            ?? $this->getTranslation('name', 'ar', false);

        return [
            'id' => $this->id,
            'name' => $localizedName,
            'name_translations' => $this->getTranslations('name'),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
