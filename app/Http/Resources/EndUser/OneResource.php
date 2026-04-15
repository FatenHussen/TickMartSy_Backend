<?php

namespace App\Http\Resources\EndUser;

use App\Http\Resources\Address\OneResource as AddressOneResource;
use App\Services\User\MarketService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $markter = null;

        if ($this->affiliate_approved && $this->affiliate_id) {
            $marketService = new MarketService();

            $markter = $marketService->getStatistics($this->affiliate_id);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'area_id' => $this->area_id,

            'affiliate' => [
                'is_affiliate'       => (bool) $this->is_affiliate,
                'affiliate_approved' => (bool) $this->affiliate_approved,
                'affiliate_id'       => $this->affiliate_approved ? $this->affiliate_id : null,
                'affiliate_rate'     => $this->affiliate_approved ? $this->affiliate_rate : null,
                'affiliate_commission_type' => $this->affiliate_approved ? $this->affiliate_commission_type : null,
                'affiliate_fixed_commission' => $this->affiliate_approved ? $this->affiliate_fixed_commission : null,
                'affiliate_visit_commission_enabled' => $this->affiliate_approved
                    ? (bool) $this->affiliate_visit_commission_enabled
                    : false,
                'affiliate_visit_commission_threshold' => $this->affiliate_approved
                    ? $this->affiliate_visit_commission_threshold
                    : null,
                'affiliate_visit_commission_amount' => $this->affiliate_approved
                    ? $this->affiliate_visit_commission_amount
                    : null,
                'affiliate_product_ids' => $this->affiliate_approved
                    ? $this->affiliateProducts()->pluck('products.id')
                    : [],
            ],

            'markter_statistics'   => $markter,
            'addresses' => AddressOneResource::collection($this->addresses),
        ];
    }
}
