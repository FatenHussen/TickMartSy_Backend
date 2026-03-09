<?php

namespace App\Http\Requests\User\Favorite;

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
            'type' => ['required', 'string', 'in:product,recipe,brand,shop,basket'],
            'id'   => ['required', 'integer'],
        ];
    }
}
