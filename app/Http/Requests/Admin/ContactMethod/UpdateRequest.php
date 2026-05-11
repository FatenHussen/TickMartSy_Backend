<?php

namespace App\Http\Requests\Admin\ContactMethod;

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
        $contactMethodId = $this->route('contact_method') ?? $this->route('contactMethod');

        return [
            'key' => ['nullable', 'string', 'max:255', Rule::unique('contact_methods', 'key')->ignore($contactMethodId)],
            'type' => ['nullable', Rule::in(['number', 'email', 'url', 'whts'])],
            'value' => 'nullable|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];
    }
}
