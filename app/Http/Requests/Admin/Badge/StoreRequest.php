<?php

namespace App\Http\Requests\Admin\Badge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'color' => 'required|string',
        ];
    }
}
