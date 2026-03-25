<?php

namespace App\Http\Requests\Admin\Vendor;

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
        $vendorId = $this->route('vendor');
        return [
            'name.ar'              => 'nullable|string|max:255',
            'name.en'              => 'nullable|string|max:255',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',

            'owner_name'           => 'nullable|string|max:255',
            'owner_phone'          => 'nullable|string|max:20',

            'commercial_register'  => 'nullable|string|max:100',
            'contract_date'        => 'nullable|date',
            'contract_number'      => ['nullable', 'required', 'string', 'unique:vendors,contract_number,' . $vendorId],
            'contract_duration_months' => 'nullable|integer|min:1',
            'commission_rate'      => 'nullable|numeric|min:0|max:100',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',

            'is_active'            => 'nullable|boolean',
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
