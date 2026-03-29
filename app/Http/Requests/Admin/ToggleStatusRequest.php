<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in($this->getAllowedTypes())
            ],
            'id' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get all allowed model types
     */
    protected function getAllowedTypes(): array
    {
        return [
            'vendor_user',
            'vendor_package',
            'vendor',
            'user_basket_schedule',
            'user',
            'system_setting',
            'store_user',
            'store',
            'shop',
            'schedule',
            'sale_country',
            'recipe',
            'promotion',
            'point_rule',
            'payment_method',
            'package',
            'media',
            'language',
            'icon',
            'currency',
            'category',
            'basket_schedule',
            'driver',
            'country',
            'brand',
            'banner',
            'faq',
            'service',
            'area',
            'city',
            'governorate',
            'badge',
            'color',
            'coupon',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => __('custom.type_required'),
            'type.in' => __('custom.invalid_type'),
            'id.required' => __('custom.id_required'),
            'id.integer' => __('custom.id_must_be_integer'),
            'is_active.required' => __('custom.is_active_required'),
            'is_active.boolean' => __('custom.is_active_must_be_boolean'),
        ];
    }
}
