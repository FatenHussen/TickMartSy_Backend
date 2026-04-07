<?php

namespace App\Http\Requests\Admin\ServiceOrder;

use App\Enums\ServiceOrderStatus;
use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'in:' . implode(',', array_column(ServiceOrderStatus::cases(), 'value')),
            ],
        ];
    }
}
