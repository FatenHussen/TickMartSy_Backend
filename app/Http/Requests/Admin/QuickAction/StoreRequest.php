<?php

namespace App\Http\Requests\Admin\QuickAction;

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
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ar' => 'required|string|max:255',
            'button_text' => 'required|array',
            'button_text.en' => 'required|string|max:255',
            'button_text.ar' => 'required|string|max:255',
            'page_id' => 'required|exists:pages,id',
            'icon' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

