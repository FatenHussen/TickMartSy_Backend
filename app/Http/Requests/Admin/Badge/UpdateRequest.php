<?php

namespace App\Http\Requests\Admin\Badge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.en' => 'nullable|string',
            'name.ar' => 'nullable|string',
            'color' => 'nullable|string',
            'image' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif',
            ],
        ];
    }

}
