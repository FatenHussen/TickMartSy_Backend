<?php

namespace App\Http\Requests\Admin\VendorPackage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'max_products' => 'required|integer|min:0',
            'is_featured' => 'boolean',
            'has_premium_badge' => 'boolean',
            'search_priority' => 'integer|min:1|max:10',
            'max_campaigns' => 'integer|min:0',
            'has_banner_ad' => 'boolean',
            'has_sales_reports' => 'boolean',
            'has_analytics' => 'boolean',
            'report_level' => 'in:basic,advanced,full',
            'order_priority' => 'integer|min:1|max:10',
            'can_set_prep_time' => 'boolean',
            'custom_shipping_options' => 'boolean',
            'has_vendor_delivery' => 'boolean',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'commission_per_order' => 'numeric|min:0',
            'activation_fee_waived' => 'boolean',
        ];
    }
}
