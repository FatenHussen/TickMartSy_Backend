<?php

namespace App\Http\Resources\Banner;

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
        return [
            'id'                    => $this->id,
            'title'                  => $this->translationPair('title'),
            'description'            => $this->translationPair('description'),
            'button_text'            => $this->translationPair('button_text'),
            'image_url'                => $this->image_url,
            'link' =>                  $this->link,
            'expires_at' =>           $this->expires_at?->format('Y-m-d H:i'),
            // 'is_active'             => $this->is_active,
            // 'order'             => $this->order,
            'is_active'             => $this->is_active,
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * Always return both locales so a cleared field comes back as null, not [].
     *
     * @return array{ar: ?string, en: ?string}
     */
    private function translationPair(string $field): array
    {
        $translations = $this->getTranslations($field);

        return [
            'ar' => $this->filledTranslation($translations['ar'] ?? null),
            'en' => $this->filledTranslation($translations['en'] ?? null),
        ];
    }

    private function filledTranslation(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
