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
            'type' => 'nullable|string|in:product,brand,shop,delivery,recipe,basket,schedule_basket,order',
            'rateable_id' => 'nullable|integer',
        ];
    }
}
