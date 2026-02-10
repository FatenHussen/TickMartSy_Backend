<?php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Translatable
            'name' => $this->name,

            'code' => $this->code,

            'discount' => [
                'type' => $this->discount_type,
                'value' => $this->discount_value,
            ],

            'start_at' => optional($this->start_at)->toDateTimeString(),
            'end_at'   => optional($this->end_at)->toDateTimeString(),

            'max_uses'   => $this->max_uses,
            'used_count' => $this->used_count,

            'is_active' => $this->is_active,

            // Helpers
            'is_expired' => $this->isExpired(),
            'is_valid'   => $this->isValid(),

            // Relations
            'city_id' => $this->city_id,
            'user_id' => $this->user_id,


            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
