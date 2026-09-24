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
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['required', 'string', 'max:255'],
            'description' => ['required', 'array'],
            'description.en' => ['required', 'string', 'max:255'],
            'description.ar' => ['required', 'string', 'max:255'],
            'button_text' => ['required', 'array'],
            'button_text.en' => ['required', 'string', 'max:255'],
            'button_text.ar' => ['required', 'string', 'max:255'],
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm|max:8192',
            'is_active' => ['nullable', 'boolean'],
            'link' => ['required', 'string', 'url'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }
}
