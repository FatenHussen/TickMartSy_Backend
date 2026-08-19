<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:255'],
            // content = standalone pages only, category = category pages only.
            'type' => ['nullable', 'in:content,category'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
