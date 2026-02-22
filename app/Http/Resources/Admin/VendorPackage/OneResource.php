<?php

namespace App\Http\Resources\Admin\VendorPackage;

use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'duration_days' => (int) $this->duration_days,
            'is_active' => (bool) $this->is_active,

            'max_products' => (int) $this->max_products,
            'is_featured' => (bool) $this->is_featured,
            'has_premium_badge' => (bool) $this->has_premium_badge,
            'search_priority' => (int) $this->search_priority,
            'max_campaigns' => (int) $this->max_campaigns,
            'has_banner_ad' => (bool) $this->has_banner_ad,
            'has_sales_reports' => (bool) $this->has_sales_reports,
            'has_analytics' => (bool) $this->has_analytics,
            'report_level' => $this->report_level,
            'order_priority' => (int) $this->order_priority,
            'can_set_prep_time' => (bool) $this->can_set_prep_time,
            'custom_shipping_options' => (bool) $this->custom_shipping_options,
            'has_vendor_delivery' => (bool) $this->has_vendor_delivery,
            'commission_rate' => (float) $this->commission_rate,
            'commission_per_order' => (float) $this->commission_per_order,
            'activation_fee_waived' => (bool) $this->activation_fee_waived,

            'subscriptions_count' => $this->subscriptions()->count(),
            'active_subscriptions_count' => $this->subscriptions()->where('status', 'active')->count(),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
