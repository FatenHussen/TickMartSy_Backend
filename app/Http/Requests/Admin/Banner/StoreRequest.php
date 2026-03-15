<?php

namespace App\Http\Requests\Admin\Banner;

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
            'title' => ['nullable', 'array'],
            'title.*' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['required', 'string', 'max:255'],

            'image'                 => 'required|image|mimes:jpeg,png,jpg,gif,webp',
            // 'is_active'            => 'nullable|boolean',
            'link' => 'nullable|string|url',
        ];
    }
}
