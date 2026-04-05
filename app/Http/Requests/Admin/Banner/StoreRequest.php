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
            'title.*' => ['required', 'string'],
            'description' => ['nullable', 'array'],
            'description.*' => ['required', 'string'],
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
            'link' => 'nullable|string|url',
            'is_active' => ['sometimes', 'boolean'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }
}
