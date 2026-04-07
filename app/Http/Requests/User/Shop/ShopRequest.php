<?php

namespace App\Http\Requests\User\Shop;

use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_id'        => 'sometimes|exists:areas,id',
            'city_id'        => 'sometimes|exists:cities,id',
            'governorate_id' => 'sometimes|exists:governorates,id',
            'category_id'    => 'sometimes|exists:categories,id',
            'brand_id'       => 'sometimes|exists:brands,id',
            'vendor_id'      => 'sometimes|exists:vendors,id',
            'is_active'      => 'sometimes|boolean',
            'is_service_provider' => 'sometimes|boolean',
            'type'           => 'sometimes|in:nearby,offers,top_rated,active',
            'search'         => 'sometimes|string|max:255',
            'lat'            => 'required_if:type,nearby|numeric|between:-90,90',
            'lng'            => 'required_if:type,nearby|numeric|between:-180,180',
            'max_distance'   => 'sometimes|numeric|min:1|max:50', // Maximum distance in KM
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required_if' => 'Latitude is required when filtering by nearby shops',
            'lng.required_if' => 'Longitude is required when filtering by nearby shops',
            'lat.between'     => 'Latitude must be between -90 and 90',
            'lng.between'     => 'Longitude must be between -180 and 180',
            'type.in'         => 'Type must be one of: nearby, offers, top_rated, active',
        ];
    }
}
