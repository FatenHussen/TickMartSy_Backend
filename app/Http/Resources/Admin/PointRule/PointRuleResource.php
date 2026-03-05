<?php

namespace App\Http\Resources\Admin\PointRule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'type' => $this->type,
            'value' => $this->value,
            'min_order_amount' => $this->min_order_amount ? (float) $this->min_order_amount : null,
            'expires_after_days' => $this->expires_after_days,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
