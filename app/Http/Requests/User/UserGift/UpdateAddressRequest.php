<?php

namespace App\Http\Requests\User\UserGift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:user_addresses,id'],
            'user_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'العنوان مطلوب',
            'address_id.exists' => 'العنوان المحدد غير موجود',
        ];
    }
}
