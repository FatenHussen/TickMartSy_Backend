<?php

namespace App\Http\Resources\ContactMethod;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'type' => $this->type,
            'value' => $this->value,
            'icon' => $this->icon_url,
        ];
    }
}
