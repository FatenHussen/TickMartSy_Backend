<?php

namespace App\Http\Resources\User\UserGift;

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
            'address' => $this->when($this->address, [
                'id' => $this->address->id ?? null,
                'full_address' => $this->address->full_address ?? null,
            ]),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
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
