<?php

namespace App\Http\Resources\Role;

use App\Http\Resources\Permission\AllResource;
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
            'permissions' => AllResource::collection($this->permissions),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

        ];
    }
}
