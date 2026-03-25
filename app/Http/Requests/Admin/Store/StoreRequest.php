<?php

namespace App\Http\Requests\Admin\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.ar'              => 'required|string|max:255',
            'name.en'              => 'required|string|max:255',
            'owner_name'           => 'required|string|max:255',
            'owner_phone'          => 'required|string|max:20',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',
            'address.ar'           => 'required|string',
            'address.en'           => 'required|string',
            'phone'                => 'nullable|string|max:20',
            'mobile'               => 'required|string|max:20',
            'email'                => 'nullable|email|unique:stores,email',
            'commercial_register'  => 'nullable|string|max:100',
            'contract_date'        => 'required|date',
            'contract_number'      => 'required|string|unique:stores,contract_number',
            'contract_duration_months' => 'required|integer|min:1',
            'commission_rate'      => 'required|numeric|min:0|max:100',
            'working_hours'        => 'required|array',
            'working_hours.*'      => 'array',
            'working_hours.*.open' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.close' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.closed' => 'sometimes|boolean',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',
            'is_active'            => 'sometimes|boolean',

            'area_ids'             => 'required|array',
            'area_ids.*'           => 'exists:areas,id',
            'service_ids'          => 'nullable|array',
            'service_ids.*'        => 'exists:services,id',
            'category_ids'         => 'required|array',
            'category_ids.*'       => 'exists:categories,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'name.ar' => 'اسم المتجر (عربي)',
            'name.en' => 'اسم المتجر (إنجليزي)',
            'owner_name' => 'اسم المالك',
            'owner_phone' => 'هاتف المالك',
            'address.ar' => 'العنوان (عربي)',
        ];
    }
}
