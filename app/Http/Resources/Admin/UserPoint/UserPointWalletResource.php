<?php

namespace App\Http\Resources\Admin\UserPoint;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPointWalletResource extends JsonResource
{
    public function toArray($request)
    {
        $wallet = $this->pointWallet;

        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            ],
            'wallet' => [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'last_earned_at' => $wallet->last_earned_at?->format('Y-m-d H:i:s'),
                'expire_at' => $wallet->expire_at?->format('Y-m-d H:i:s'),
                'created_at' => $wallet->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $wallet->updated_at?->format('Y-m-d H:i:s'),
            ],
            'statistics' => [
                'total_transactions' => $wallet->transactions()->count(),
                'total_earned' => $wallet->transactions()->where('status', 'earned')->sum('points'),
                'total_redeemed' => abs($wallet->transactions()->where('status', 'redeemed')->sum('points')),
                'pending_transactions' => $wallet->transactions()->where('status', 'pending')->count(),
            ],
            'recent_transactions' => UserPointTransactionResource::collection(
                $wallet->transactions()->latest()->limit(10)->get()
            ),
        ];
    }
}

