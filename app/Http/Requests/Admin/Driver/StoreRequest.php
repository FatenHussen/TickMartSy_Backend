<?php

namespace App\Http\Requests\Admin\Driver;

use App\Http\Requests\Concerns\ValidatesDriverCityScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    use ValidatesDriverCityScope;

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForDriverCityScope($validator);
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'phone' => ['required', 'unique:drivers,phone'],
            'email' => ['nullable', 'email', 'unique:drivers,email'],
            'password' => ['required', 'min:8'],
            'is_active'            => 'nullable|boolean',
            'address' => 'nullable|string',
            'status' => 'nullable|in:available,busy,inactive',
            'city_ids' => 'nullable|array',
            'city_ids.*.id' => 'required|integer|exists:cities,id',
            'shop_ids' => 'nullable|array',
            'shop_ids.*.id' => 'required|integer|exists:shops,id',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*.id' => 'required|integer|exists:vendors,id',
            'rate_per_order' => 'required',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_name' => 'required|string|max:255',
            'vehicle_number' => 'required',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'vehicle_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',

        ];
    }
}
