<?php

namespace App\Http\Resources\Driver;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'rate_per_order' => $this->rate_per_order,
            'areas' => AreaOneResource::collection($this->areas),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

        ];
    }
}
