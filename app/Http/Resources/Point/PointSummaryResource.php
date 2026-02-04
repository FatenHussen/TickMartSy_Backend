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
            'pending_points' => $this->resource['pending_points'],
            'expired_points' => $this->resource['expired_points'],
            'redeemed_points' => $this->resource['redeemed_points'],
            'expire_at' => $this->resource['expire_at'] ? $this->resource['expire_at']->format('Y-m-d') : null,
            'last_earned_at' => $this->resource['last_earned_at'] ? $this->resource['last_earned_at']->format('Y-m-d') : null,
        ];
    }
}