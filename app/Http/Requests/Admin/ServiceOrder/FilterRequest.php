<?php

namespace App\Http\Requests\Admin\ServiceOrder;

use App\Enums\ServiceOrderStatus;
use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                'in:' . implode(',', array_column(ServiceOrderStatus::cases(), 'value')),
            ],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'vendor_service_id' => ['nullable', 'exists:vendor_services,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
