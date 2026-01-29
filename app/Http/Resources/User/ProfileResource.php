<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'name' => $this->name ?? 'User',
            'phone' => $this->phone ?? 'Phone',
            'email' => $this->email ?? 'Email',
            'city' => $this->city?->name ?? null,
            'image' => $this->image_url,
        ];
    }
}