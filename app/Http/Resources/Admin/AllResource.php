<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\User\City\CityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            'roles' => $this->roles->pluck('name')->values(),
            'cities' => CityResource::collection($this->whenLoaded('cities')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

        ];
    }
}
