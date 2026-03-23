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
            'rating_min' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'rating_max' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'serves' => ['nullable', 'string', 'max:50'],
            'prepare_time' => ['nullable', 'string', 'max:50'],
            'has_discount' => ['nullable', 'boolean'],
            'type' => ['nullable', 'in:newest,popular,top_rated,on_sale'],
            'sort_by' => ['nullable', 'in:newest,oldest,price_asc,price_desc,rating_desc,rating_asc,popular,discount_desc'],
        ];
    }
}
