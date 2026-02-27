<?php

namespace App\Http\Resources\User\UserGift;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'gift' => [
                'id' => $this->gift->id,
                'name' => $this->gift->name,
                'description' => $this->gift->description,
                'image' => $this->gift->image ? asset('storage/' . $this->gift->image) : null,
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
            'status_label' => $this->getStatusLabel(),
            'user_notes' => $this->user_notes,
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    protected function getStatusLabel()
    {
        $locale = app()->getLocale();
        $labels = [
            'pending' => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
            'processing' => ['ar' => 'قيد المعالجة', 'en' => 'Processing'],
            'shipped' => ['ar' => 'تم الشحن', 'en' => 'Shipped'],
            'delivered' => ['ar' => 'تم التسليم', 'en' => 'Delivered'],
            'cancelled' => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$this->status][$locale] ?? $this->status;
    }
}
