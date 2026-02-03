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
            'points_currency' => $pointsSettings['currency_symbol'] . number_format(abs($this->points) * $pointsSettings['currency_rate'], 2),
            'source' => $this->source,
            'status' => $this->status,
            'type' => $this->getTransactionType(),
            'reason' => $this->reason,
            'rule' => $this->when($this->rule, [
                'title' => $this->rule?->title,
                'code' => $this->rule?->code,
            ]),
            'admin' => $this->when($this->admin, [
                'name' => $this->admin?->name,
                'id' => $this->admin?->id,
            ]),
            'reference' => $this->when($this->reference_type, [
                'type' => $this->reference_type,
                'id' => $this->reference_id,
            ]),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'expires_at_formatted' => $this->expires_at?->format('d/m/Y'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('d/m/Y'),
        ];
    }

    /**
     * Get transaction type based on status and points value
     */
    private function getTransactionType(): string
    {
        return match ($this->status) {
            'pending' => 'نقاط معلقة / Pending Points',
            'earned' => $this->points > 0 ? 'نقاط مكتسبة / Points Earned' : 'خصم نقاط / Points Deducted',
            'expired' => 'نقاط منتهية / Points Expired',
            'redeemed' => 'نقاط مستبدلة / Points Exchanged',
            default => 'غير معروف / Unknown',
        };
    }
}