<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'key' => $this->key,
            'title' => $this->getTranslation('title', $locale),
            'content' => $this->getTranslation('content', $locale),
        ];
    }
}
