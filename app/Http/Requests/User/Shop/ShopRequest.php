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
            'area_id'        => 'sometimes|required_if:type,zone|exists:areas,id',
            'city_id'        => 'sometimes|exists:cities,id',
            'governorate_id' => 'sometimes|exists:governorates,id',
            'category_id'    => 'sometimes|exists:categories,id',
            'brand_id'       => 'sometimes|exists:brands,id',
            'vendor_id'      => 'sometimes|exists:vendors,id',
            'is_active'      => 'sometimes|boolean',
            'is_service_provider' => 'sometimes|boolean',
            'is_restaurant'  => 'sometimes|boolean',
            'shop_type'      => 'sometimes|in:restaurant,service_provider,store',
            'type'           => 'sometimes|in:nearby,near_me,offers,top_rated,most_rated,active,open,close,newest,zone',
            'is_open_now'    => 'sometimes|boolean',
            'pricing_tier'   => 'sometimes|in:cheap,medium,expensive',
            'search'         => 'sometimes|string|max:255',
            'lat'            => 'required_if:type,nearby,near_me|numeric|between:-90,90',
            'lng'            => 'required_if:type,nearby,near_me|numeric|between:-180,180',
            'sort_by'        => 'sometimes|in:newest,oldest,most_rated,rating_desc,rating_asc,near_me',
            'max_distance'   => 'sometimes|numeric|min:1|max:50', // Maximum distance in KM
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required_if' => 'Latitude is required when filtering by nearby/near_me shops',
            'lng.required_if' => 'Longitude is required when filtering by nearby/near_me shops',
            'lat.between'     => 'Latitude must be between -90 and 90',
            'lng.between'     => 'Longitude must be between -180 and 180',
            'type.in'         => 'Type must be one of: nearby, near_me, offers, top_rated, most_rated, active, open, close, newest, zone',
            'shop_type.in'    => 'Shop type must be one of: restaurant, service_provider, store',
        ];
    }
}
