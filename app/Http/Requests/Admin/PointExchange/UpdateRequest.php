<?php

namespace App\Http\Requests\Admin\PointExchange;

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
            'status' => ['sometimes', 'required', 'string', 'in:pending,completed,cancelled'],
            'delivered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
