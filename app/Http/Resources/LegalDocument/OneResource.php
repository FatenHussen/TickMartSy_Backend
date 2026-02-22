<?php

namespace App\Http\Resources\LegalDocument;

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
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->getTranslations('title'),
            'content' => $this->getTranslations('content'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
