<?php

namespace App\Http\Requests\User\Product;

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
            'category_id'   => ['nullable', 'exists:categories,id'],
            'price_min'     => ['nullable', 'numeric', 'min:0'],
            'price_max'     => ['nullable', 'numeric', 'min:0'],
            'country'       => ['nullable', 'string', 'max:100'],
            'name'          => ['nullable', 'string', 'max:100'],

            'type'          => ['nullable', 'in:new,trend,top_rated,offers,recommended,for_you,search_based'],
            'search'        => ['nullable', 'string', 'max:255'],
        ];
    }
}
