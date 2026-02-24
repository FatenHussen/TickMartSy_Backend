<?php

namespace App\Http\Resources\PointExchange;

use Illuminate\Http\Resources\Json\JsonResource;

class PointExchangeResource extends JsonResource
{
    public function toArray($request)
    {
        $exchangeData = $this->exchange_data ?? [];

        return [
            'id' => $this->id,
            'exchange_type' => $this->exchange_type,
            'exchange_type_label' => $this->getExchangeTypeLabel(),
            'points_used' => $this->points_used,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'details' => $this->getExchangeDetails(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'expires_at' => $exchangeData['expires_at'] ?? null,
        ];
    }

    private function getExchangeTypeLabel(): string
    {
        return match($this->exchange_type) {
            'coupon' => 'كوبون خصم',
            'free_delivery' => 'توصيل مجاني',
            'gift' => 'هدية',
            default => $this->exchange_type,
        };
    }

    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'قيد الانتظار',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'expired' => 'منتهي',
            default => $this->status,
        };
    }

    private function getExchangeDetails(): array
    {
        $data = $this->exchange_data ?? [];

        switch ($this->exchange_type) {
            case 'coupon':
                return [
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'coupon_code' => $data['coupon_code'] ?? null,
                ];

            case 'free_delivery':
                return [
                    'delivery_zones' => $data['delivery_zones'] ?? [],
                ];

            case 'gift':
                $gift = \App\Models\Gift::find($data['gift_id'] ?? null);
                return [
                    'gift_id' => $data['gift_id'] ?? null,
                    'gift_name' => $gift?->name ?? 'غير متوفر',
                    'gift_image' => $gift && $gift->image ? asset('storage/' . $gift->image) : null,
                    'delivery_address' => $data['delivery_address'] ?? null,
                ];

            default:
                return $data;
        }
    }
}
