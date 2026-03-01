<?php

namespace App\Http\Requests\Admin\Shop;

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

            'address.ar'           => 'required|string',
            'address.en'           => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',

            'phone'                => 'nullable|string|max:20',
            'mobile'               => 'required|string|max:20',
            'email'                => 'nullable|email|unique:stores,email',

            'working_hours'        => 'required|array',
            'working_hours.*'      => 'array',
            'working_hours.*.open' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.close' => 'required_without:working_hours.*.closed|date_format:H:i',
            'working_hours.*.closed' => 'sometimes|boolean',

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'is_active'            => 'sometimes|boolean',

            'area_id'           => 'required|exists:areas,id',
            'vendor_id' => 'required|exists:vendors,id',

            'service_ids'          => 'nullable|array',
            'service_ids.*.id'  => 'required|integer|exists:services,id',


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
