<?php

namespace App\Http\Requests\User\Rating;

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
            'rateable_id' => ['nullable', 'integer'],
            'rateable_type' => ['nullable', 'string', 'in:product,delivery,basket,schedule_basket,shop,recipe,brand'],
            'rating' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
        ];
    }
}
