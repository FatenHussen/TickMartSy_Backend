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
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
