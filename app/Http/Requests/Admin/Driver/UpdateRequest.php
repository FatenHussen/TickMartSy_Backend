<?php

namespace App\Http\Requests\Admin\Driver;

use App\Http\Requests\Concerns\ValidatesDriverCityScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
{
    use ValidatesDriverCityScope;

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForDriverCityScope($validator);
    }

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
        $Id = $this->route('driver');
        return [
            'name'              => 'nullable|string|max:255',
            'phone' => ['nullable', 'unique:drivers,phone,' . $Id],
            'email' => ['nullable', 'email', 'unique:drivers,email,' . $Id],
            'password' => ['nullable'],
            'is_active'            => 'nullable|boolean',
            'address' => 'nullable|string',
            'status' => 'nullable|in:available,busy,inactive',
            'city_ids' => 'nullable|array',
            'city_ids.*.id' => 'required|integer|exists:cities,id',
            'shop_ids' => 'nullable|array',
            'shop_ids.*.id' => 'required|integer|exists:shops,id',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*.id' => 'required|integer|exists:vendors,id',
            'rate_per_order' => 'nullable',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'vehicle_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',

        ];
    }
}
