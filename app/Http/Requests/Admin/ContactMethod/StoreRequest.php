<?php

namespace App\Http\Requests\Admin\ContactMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'nullable|string|max:255|unique:contact_methods,key',
            'type' => ['required', Rule::in(['number', 'email', 'url', 'whts'])],
            'value' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];
    }
}
