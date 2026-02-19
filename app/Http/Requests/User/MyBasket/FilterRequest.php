<?php

namespace App\Http\Requests\User\MyBasket;

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
            'type' => ['nullable', 'string', 'in:subscription,custom,user-schedule,all'],
        ];
    }
}
