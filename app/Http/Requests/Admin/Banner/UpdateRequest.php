<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'array'],
            'title.*' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['required', 'string', 'max:255'],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'is_active' => ['nullable', 'boolean'],
            'link' => 'nullable|string|url',
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
