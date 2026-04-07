<?php

namespace App\Http\Requests\User\ServiceOrder;

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
            'shop_id' => ['required', 'exists:shops,id'],
            'vendor_service_id' => ['required', 'exists:vendor_services,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
