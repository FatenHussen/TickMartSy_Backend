<?php

namespace App\Http\Requests\User\Rating;

use Illuminate\Foundation\Http\FormRequest;

class MyRatingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|string|in:product,brand,shop,delivery,recipe,basket,scheduled_basket',
            'rateable_id' => 'nullable|integer',
        ];
    }
}
