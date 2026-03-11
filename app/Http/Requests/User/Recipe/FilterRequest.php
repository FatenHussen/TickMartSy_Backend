<?php

namespace App\Http\Requests\User\Recipe;

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
            'search' => ['nullable', 'string', 'max:255'],
            'discount_min' => ['nullable', 'numeric', 'min:0'],
            'discount_max' => ['nullable', 'numeric', 'min:0'],
            'serves' => ['nullable', 'string', 'max:50'],
            'prepare_time' => ['nullable', 'string', 'max:50'],
            'sort_by' => ['nullable', 'in:newest,oldest,rating_desc,rating_asc'],
        ];
    }
}
