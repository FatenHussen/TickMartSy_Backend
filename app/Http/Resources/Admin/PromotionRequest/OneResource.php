<?php

namespace App\Http\Resources\Admin\PromotionRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),

            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->name,
                'email' => $this->vendor?->email,
                'phone' => $this->vendor?->phone,
            ],

            'shop' => [
                'id' => $this->shop?->id,
                'name' => $this->shop?->name,
                'address' => $this->shop?->address,
                'phone' => $this->shop?->phone,
            ],

            'images' => $this->images ? array_map(fn($img) => asset('storage/' . $img), $this->images) : [],

            // Offer details
            'discount_percentage' => $this->discount_percentage,
            'offer_starts_at' => $this->offer_starts_at?->format('Y-m-d'),
            'offer_ends_at' => $this->offer_ends_at?->format('Y-m-d'),

            // Banner details
            'banner_position' => $this->banner_position,
            'link_url' => $this->link_url,
            'banner_starts_at' => $this->banner_starts_at?->format('Y-m-d'),
            'banner_ends_at' => $this->banner_ends_at?->format('Y-m-d'),

            // Admin review
            'admin_notes' => $this->admin_notes,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'approved_by' => [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
            ],

            'is_expired' => $this->isExpired(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
