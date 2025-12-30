<?php

namespace App\Http\Resources\Permission;

use App\Http\Resources\Role\AllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'roles' => AllResource::collection($this->roles),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

        ];
    }
}
