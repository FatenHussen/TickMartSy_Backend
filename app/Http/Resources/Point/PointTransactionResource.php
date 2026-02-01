<?php

namespace App\Http\Resources\Point;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Get point to currency conversion settings from database
        $pointsSettings = \App\Helpers\SettingsHelper::getPointsSettings();
        
        return [
            'id' => $this->id,
            'points' => $this->points,
            'points_currency' => $pointsSettings['currency_symbol'] . number_format($this->points * $pointsSettings['currency_rate'], 2),
            'source' => $this->source,
            'status' => $this->status,
            'type' => $this->getTransactionType(),
            'reason' => $this->reason,
            'rule' => $this->when($this->rule, [
                'title' => $this->rule?->title,
                'code' => $this->rule?->code,
            ]),
            'reference' => $this->when($this->reference_type, [
                'type' => $this->reference_type,
                'id' => $this->reference_id,
            ]),
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Get transaction type based on status and points value
     */
    private function getTransactionType(): string
    {
        return match ($this->status) {
            'pending' => 'pending_earning',
            'earned' => $this->points > 0 ? 'points_earned' : 'points_deducted',
            'expired' => 'points_expired',
            'redeemed' => 'points_redeemed',
            default => 'unknown',
        };
    }
}