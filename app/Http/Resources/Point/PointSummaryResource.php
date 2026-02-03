<?php

namespace App\Http\Resources\Point;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Get point to currency conversion settings from database
        $pointsSettings = \App\Helpers\SettingsHelper::getPointsSettings();
        
        return [
            'balance' => $this->resource['balance'],
            'balance_currency' => $pointsSettings['currency_symbol'] . number_format($this->resource['balance'] * $pointsSettings['currency_rate'], 2),
            'pending_points' => $this->resource['pending_points'],
            'pending_points_currency' => $pointsSettings['currency_symbol'] . number_format($this->resource['pending_points'] * $pointsSettings['currency_rate'], 2),
            'expired_points' => $this->resource['expired_points'],
            'expired_points_currency' => $pointsSettings['currency_symbol'] . number_format($this->resource['expired_points'] * $pointsSettings['currency_rate'], 2),
            'redeemed_points' => $this->resource['redeemed_points'],
            'redeemed_points_currency' => $pointsSettings['currency_symbol'] . number_format($this->resource['redeemed_points'] * $pointsSettings['currency_rate'], 2),
            'expire_at' => $this->resource['expire_at'] ? $this->resource['expire_at']->format('Y-m-d H:i:s') : null,
            'expire_at_formatted' => $this->resource['expire_at'] ? $this->resource['expire_at']->format('d/m/Y') : null,
            'last_earned_at' => $this->resource['last_earned_at'] ? $this->resource['last_earned_at']->format('Y-m-d H:i:s') : null,
            'last_earned_at_formatted' => $this->resource['last_earned_at'] ? $this->resource['last_earned_at']->format('d/m/Y') : null,
        ];
    }
}