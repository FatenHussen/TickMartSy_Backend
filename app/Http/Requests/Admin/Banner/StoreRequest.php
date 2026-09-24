<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('expires_at') && $this->input('expires_at') === '') {
            $this->merge(['expires_at' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'array'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'button_text' => ['nullable', 'array'],
            'button_text.en' => ['nullable', 'string', 'max:255'],
            'button_text.ar' => ['nullable', 'string', 'max:255'],
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm|max:8192',
            'link' => ['nullable', 'string', 'url'],
            'is_active' => ['sometimes', 'boolean'],
            // فارغ / غير مرسل = دائم (لا يُحذف تلقائياً)
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
