<?php

namespace App\Http\Resources\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->localized_name,
            // 'localized_name' => $this->localized_name,
            'symbol' => $this->symbol,
            'exchange_rate' => $this->exchange_rate,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ];
    }
}
