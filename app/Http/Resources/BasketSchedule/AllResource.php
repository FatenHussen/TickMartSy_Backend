<?php

namespace App\Http\Resources\BasketSchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'discount_type'  => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'number_of_days'  => $this->number_of_days,
            'is_default'     => (bool) $this->is_default,
        ];
    }
}
