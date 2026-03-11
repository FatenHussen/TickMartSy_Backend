<?php

namespace App\Http\Requests\User\Shop;

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
            'governorate_id' => ['nullable', 'exists:governorates,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'in:newest,oldest,rating_desc,rating_asc'],
        ];
    }
}
