<?php

namespace App\Http\Resources\ContactMethod;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'value' => $this->value,
            'icon' => $this->icon_url,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
