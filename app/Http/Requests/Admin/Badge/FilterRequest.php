<?php

namespace App\Http\Requests\Admin\Badge;

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
            'type'   => 'nullable|in:orders,delivery,payments,account,stores&drivers,other',
        ];
    }
}
