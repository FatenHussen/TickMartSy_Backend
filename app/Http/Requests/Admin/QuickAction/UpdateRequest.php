<?php

namespace App\Http\Requests\Admin\QuickAction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|array',
            'title.en' => 'sometimes|string|max:255',
            'title.ar' => 'sometimes|string|max:255',
            'button_text' => 'sometimes|array',
            'button_text.en' => 'sometimes|string|max:255',
            'button_text.ar' => 'sometimes|string|max:255',
            'page_id' => 'sometimes|exists:pages,id',
            'icon' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

