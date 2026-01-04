<?php

namespace App\Http\Resources\Admin\Language;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'native_name' => $this->native_name,
            'direction' => $this->direction,
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) $this->is_default,
            'order' => $this->order,
            'flag_icon' => $this->flag_icon,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
