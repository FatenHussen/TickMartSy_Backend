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
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.ar' => ['required', 'string', 'max:255'],
            'description' => ['required', 'array'],
            'description.en' => ['required', 'string'],
            'description.ar' => ['required', 'string'],
            'button_text' => ['required', 'array'],
            'button_text.en' => ['required', 'string', 'max:255'],
            'button_text.ar' => ['required', 'string', 'max:255'],
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm|max:8192',
            'link' => ['required', 'string', 'url'],
            'is_active' => ['sometimes', 'boolean'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }
}
