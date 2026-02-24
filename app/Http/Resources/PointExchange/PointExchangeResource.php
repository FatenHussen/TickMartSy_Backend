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
            'status' => $this->status,
            'details' => $this->getExchangeDetails(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'expires_at' => $exchangeData['expires_at'] ?? null,
        ];
    }



    private function getExchangeDetails(): array
    {
        $data = $this->exchange_data ?? [];

        switch ($this->exchange_type) {
            case 'coupon':
                return [
                    'discount_amount' => $data['discount_amount'] ?? 0,
                ];

            case 'free_delivery':
                return [
                    'delivery_zones' => $data['delivery_zones'] ?? [],
                ];


            default:
                return $data;
        }
    }
}
