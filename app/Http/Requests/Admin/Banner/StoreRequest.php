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
            'button_text' => ['nullable', 'array'],
            'button_text.*' => ['required', 'string', 'max:255'],
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm',
            'link' => 'nullable|string|url',
            'is_active' => ['sometimes', 'boolean'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }
}
