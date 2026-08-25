<?php

namespace App\Http\Requests\Admin\CustomOrderRequest;

use App\Enums\CustomOrderRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'string',
                Rule::in(array_column(CustomOrderRequestStatus::cases(), 'value')),
            ],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
