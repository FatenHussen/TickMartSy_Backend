<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'is_active' => $this->is_active,
        ];
    }
}
