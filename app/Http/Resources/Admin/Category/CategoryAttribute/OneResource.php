<?php

namespace App\Http\Resources\Admin\Category\CategoryAttribute;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        return [
            'id'       => $this->id,
            'name'     => $this->getTranslations('name'),
            'category' => $this->category->getTranslation('name', $locale),
            'values'   => $this->values->map(fn($value) => [
                'id'   => $value->id,
                'name' => $value->getTranslations('name'),
            ]),
        ];
    }
}
