<?php

namespace App\Http\Requests\Admin\UserGift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gift_id' => ['sometimes', 'integer', 'exists:gifts,id'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'address_id' => ['nullable', 'integer', 'exists:user_addresses,id'],
            'status' => ['sometimes', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'gift_id.exists' => 'الهدية المحددة غير موجودة',
            'user_id.exists' => 'المستخدم المحدد غير موجود',
            'address_id.exists' => 'العنوان المحدد غير موجود',
            'status.in' => 'الحالة غير صالحة',
        ];
    }
}
