<?php

namespace App\Http\Requests\User\CustomOrderRequest;

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
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'address_id' => ['required', 'exists:user_addresses,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'expected_at' => ['nullable', 'date', 'after_or_equal:now'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
