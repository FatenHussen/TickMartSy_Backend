<?php

namespace App\Http\Requests\Admin\NavMenuItem;

use Illuminate\Foundation\Http\FormRequest;

class SortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['required', 'integer', 'distinct', 'exists:nav_menu_items,id'],
        ];
    }
}
