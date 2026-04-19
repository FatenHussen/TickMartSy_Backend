<?php

namespace App\Http\Requests\Admin\Shop;

use App\Http\Requests\Concerns\ValidatesShopAreaCityScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    use ValidatesShopAreaCityScope;

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForShopAreaCityScope($validator);
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

            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'cover_images'         => 'nullable|array',
            'cover_images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp',

            'is_active'            => 'sometimes|boolean',
            'is_restaurant'        => 'sometimes|boolean',
            'payment_methods'      => 'nullable|array',
            'payment_methods.*'    => 'required|string|in:cash,online',
            'pricing_tier'         => 'nullable|in:cheap,medium,expensive',
            'is_recommended'       => 'sometimes|boolean',

            'area_id'           => 'required|exists:areas,id',
            'vendor_id' => 'required|exists:vendors,id',

            'service_ids'          => 'nullable|array',
            'service_ids.*.id'  => 'required|integer|exists:services,id',


            'badges'          => 'nullable|array',
            'badges.*' => 'integer|exists:badges,id',
            'coupon_ids'      => 'nullable|array',
            'coupon_ids.*'    => 'integer|exists:coupons,id',

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
