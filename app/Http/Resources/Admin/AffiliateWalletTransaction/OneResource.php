<?php

namespace App\Http\Resources\Admin\AffiliateWalletTransaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'affiliate_id' => $this->affiliate_id,
            'affiliate' => [
                'id' => $this->affiliate?->id,
                'name' => $this->affiliate?->name,
                'email' => $this->affiliate?->email,
                'phone' => $this->affiliate?->phone,
                'image_url' => $this->affiliate?->image_url,
            ],
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'order_id' => $this->order_id,
            'order' => $this->order ? [
                'id' => $this->order->id,
                'status' => $this->order->status ?? null,
            ] : null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
