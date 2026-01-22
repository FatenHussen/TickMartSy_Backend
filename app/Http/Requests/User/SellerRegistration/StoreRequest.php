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

            'address' => 'nullable|string|max:500',
            'commercial_register_number' => 'nullable|string|max:255',
            'commercial_register_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gender' => 'nullable|in:male,female',
            'country' => 'nullable|string|max:100',

            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
