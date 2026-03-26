<?php

namespace App\Http\Requests\Admin\Vendor;

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
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',

            'owner_name'           => 'required|string|max:255',
            'owner_phone'          => 'required|string|max:20',

            'commercial_register'  => 'nullable|string|max:100',
            'contract_date'        => 'required|date',
            'contract_number'      => 'required|string|unique:vendors,contract_number',
            'contract_duration_months' => 'required|integer|min:1',
            'commission_rate'      => 'required|numeric|min:0|max:100',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',

            'is_active'            => 'sometimes|boolean',
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
