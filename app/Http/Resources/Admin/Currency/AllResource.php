<?php

namespace App\Http\Resources\Admin\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'localized_name' => $this->localized_name,
            'symbol' => $this->symbol,
            'exchange_rate' => $this->exchange_rate,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'users_count' => $this->users_count ?? 0,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
