<?php

namespace App\Http\Resources\Admin\AffiliateWithdrawRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
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
            ],
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'note' => $this->note,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
