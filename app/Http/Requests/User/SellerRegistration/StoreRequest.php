<?php

namespace App\Http\Requests\User\SellerRegistration;

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
            'email' => 'required|email|unique:seller_registrations,email',
            'password' => 'required|string|min:8',

            'seller_name' => 'required|string|max:255',
            'store_name'  => 'required|string|max:255',

            'address' => 'required|string|max:500',
            'commercial_register_number' => 'nullable|string|max:255',
            'commercial_register_date' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'governorate_id' => 'required|exists:governorates,id',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ];
    }
}
