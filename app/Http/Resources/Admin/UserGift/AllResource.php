<?php

namespace App\Http\Resources\Admin\UserGift;

use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'gift' => [
                'id' => $this->gift->id,
                'name' => $this->gift->name,
                'image' => $this->gift->image ? asset('storage/' . $this->gift->image) : null,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ],
            'address' => $this->when($this->address, function () {
                return [
                    'id' => $this->address->id,
                    'full_address' => $this->address->full_address,
                ];
            }),
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
