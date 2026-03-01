<?php

namespace App\Http\Requests\Admin\Shop;

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
        $shopId = $this->route('shop');

        return [
            'name.ar'              => 'nullable|string|max:255',
            'name.en'              => 'nullable|string|max:255',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',

            'address.ar'           => 'nullable|string',
            'address.en'           => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',

            'phone'                => 'nullable|string|max:20',
            'mobile'               => 'nullable|string|max:20',
            'email'                => ['nullable', 'email', 'unique:shops,email,' . $shopId],

            'working_hours'        => 'nullable|array',
            'working_hours.*'      => 'array',
            'working_hours.*.open' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.close' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.closed' => 'sometimes|boolean',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'is_active'            => 'nullable|boolean',
            'area_id'           => 'nullable|exists:areas,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'service_ids'          => 'nullable|array',
            'service_ids.*'        => 'exists:services,id',

            'badges'          => 'nullable|array',
            'badges.*.id'  => 'required|integer|exists:badges,id',
            'badges.*.position'  => 'required|in:top,bottom',
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
