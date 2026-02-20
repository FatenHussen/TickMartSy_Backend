<?php

namespace App\Http\Resources\Admin\UserPoint;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPointSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        $wallet = $this->pointWallet;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'wallet' => [
                'balance' => $wallet?->balance ?? 0,
                'last_earned_at' => $wallet?->last_earned_at?->format('Y-m-d H:i:s'),
                'expire_at' => $wallet?->expire_at?->format('Y-m-d H:i:s'),
            ],
            'total_transactions' => $wallet?->transactions()->count() ?? 0,
            'total_earned' => $wallet?->transactions()->where('status', 'earned')->sum('points') ?? 0,
            'total_redeemed' => abs($wallet?->transactions()->where('status', 'redeemed')->sum('points') ?? 0),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
