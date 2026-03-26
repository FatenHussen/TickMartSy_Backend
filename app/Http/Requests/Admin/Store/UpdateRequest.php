<?php

namespace App\Http\Requests\Admin\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $storeId = $this->route('store')->id;

        return [
            'name.ar'              => 'nullable|string|max:255',
            'name.en'              => 'nullable|string|max:255',
            'owner_name'           => 'nullable|string|max:255',
            'owner_phone'          => 'nullable|string|max:20',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',
            'address.ar'           => 'nullable|string',
            'address.en'           => 'nullable|string',
            'phone'                => 'nullable|string|max:20',
            'mobile'               => 'nullable|string|max:20',
            'email'                => ['nullable', 'email', 'unique:stores,email,' . $storeId],
            'commercial_register'  => 'nullable|string|max:100',
            'contract_date'        => 'nullable|date',
            'contract_number'      => ['nullable', 'required', 'string', 'unique:stores,contract_number,' . $storeId],
            'contract_duration_months' => 'nullable|integer|min:1',
            'commission_rate'      => 'nullable|numeric|min:0|max:100',
            'working_hours'        => 'nullable|array',
            'working_hours.*'      => 'array',
            'working_hours.*.open' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.close' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.closed' => 'sometimes|boolean',
            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',
            'is_active'            => 'nullable|boolean',
            'area_ids'             => 'nullable|array',
            'area_ids.*'           => 'exists:areas,id',
            'service_ids'          => 'nullable|array',
            'service_ids.*'        => 'exists:services,id',
            'category_ids'         => 'nullable|array',
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
