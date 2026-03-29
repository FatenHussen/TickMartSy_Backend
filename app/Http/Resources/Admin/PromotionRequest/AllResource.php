<?php

namespace App\Http\Resources\Admin\PromotionRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'title' => $this->title,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),

            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
            ],

            'shop' => [
                'id' => $this->shop?->id,
                'name' => $this->shop?->name,
            ],

            'discount_percentage' => $this->discount_percentage,
            'offer_starts_at' => $this->offer_starts_at?->format('Y-m-d'),
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),
            'banner_starts_at' => $this->banner_starts_at?->format('Y-m-d'),
            'banner_ends_at' => $this->banner_ends_at?->format('Y-m-d'),
            'is_active' => $this->is_active,

            'is_expired' => $this->isExpired(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
