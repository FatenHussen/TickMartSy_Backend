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

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['title', 'description', 'button_text'] as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $value = $this->input($field);
            if (! is_array($value)) {
                $merge[$field] = ['ar' => null, 'en' => null];
                continue;
            }

            foreach ($value as $locale => $text) {
                if (is_string($text) && trim($text) === '') {
                    $value[$locale] = null;
                }
            }

            $merge[$field] = $value;
        }

        foreach (['link', 'expires_at'] as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $value = $this->input($field);
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
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
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:255'],
            'description.ar' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'array'],
            'button_text.en' => ['nullable', 'string', 'max:255'],
            'button_text.ar' => ['nullable', 'string', 'max:255'],
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm|max:8192',
            'is_active' => ['nullable', 'boolean'],
            'link' => ['nullable', 'string', 'url'],
            // فارغ / غير مرسل = دائم (لا يُحذف تلقائياً)
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
