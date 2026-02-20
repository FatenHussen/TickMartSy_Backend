<?php

namespace App\Http\Resources\Point;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PointRule;
use App\Models\PointTransaction;

class PointSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pointsSettings = \App\Helpers\SettingsHelper::getPointsSettings();
        $balance = $this->resource['balance'];
        $currencyRate = $pointsSettings['currency_rate'];
        $currencySymbol = $pointsSettings['currency_symbol'];

        $estimatedValue = $balance * $currencyRate;

        $nextReward = $balance > 0 ? (ceil($balance / 1000) * 1000) : 1000;

        $nextExpiry = $this->resource['expire_at'];

        $earningRules = $this->getEarningRules();

        return [
            'points' =>  $balance,
            'value' => [
                'point_value' => "1 pt = {$currencyRate} {$currencySymbol}",
                'estimated_value' => number_format($estimatedValue, 0) . " {$currencySymbol} in rewards",
            ],
            'next_reward' => "Next reward at " . number_format($nextReward, 0) . " pts",

            'expiry' => $nextExpiry->format('Y-m-d'),
            'earning_rules' => $earningRules,
        ];
    }

    private function getEarningRules(): array
    {
        $rules = PointRule::where('is_active', true)
            ->orderBy('id')
            ->get();

        return $rules->map(function ($rule) {
            return $rule->title;
        })->toArray();
    }
}
