<?php

namespace App\Http\Requests\Admin\Shop;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'is_active' => 'nullable|boolean',
            'shop_status' => 'nullable|in:open,closed,active,inactive',
            'is_service_provider' => 'nullable|boolean',
            'is_restaurant' => 'nullable|boolean',
            'shop_type' => 'nullable|in:restaurant,service_provider,store',
            'is_recommended' => 'nullable|boolean',
            'pricing_tier' => 'nullable|in:cheap,medium,expensive',
        ];
    }
}
