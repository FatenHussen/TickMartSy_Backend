<?php

namespace App\Http\Resources\Admin\UserGift;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'gift' => [
                'id' => $this->gift->id,
                'name' => $this->gift->getTranslations('name'),
                'description' => $this->gift->getTranslations('description'),
                'image' => $this->gift->image ? asset('storage/' . $this->gift->image) : null,
                'points_required' => $this->gift->points_required,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ],
            'address' => $this->when($this->address, function () {
                return [
                    'id' => $this->address->id,
                    'full_address' => $this->address->full_address,
                    'city' => $this->address->city->name ?? null,
                    'area' => $this->address->area->name ?? null,
                ];
            }),
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'user_notes' => $this->user_notes,
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
