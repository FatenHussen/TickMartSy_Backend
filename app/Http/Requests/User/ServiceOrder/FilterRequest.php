<?php

namespace App\Http\Requests\User\ServiceOrder;

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
        ];
    }
}
