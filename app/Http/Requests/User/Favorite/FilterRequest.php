<?php

namespace App\Http\Requests\User\Favorite;

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
            'rateable_type' => ['nullable', 'string', 'in:product,delivery,basket,schedule_basket,shop,recipe,brand'],
            'type' => 'nullable|in:product,recipe,brand,basket',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'category_id' => 'nullable|integer|exists:categories,id',
        ];
    }
}
