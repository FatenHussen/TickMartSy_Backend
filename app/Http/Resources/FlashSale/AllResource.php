<?php

namespace App\Http\Resources\FlashSale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ];
    }
}
