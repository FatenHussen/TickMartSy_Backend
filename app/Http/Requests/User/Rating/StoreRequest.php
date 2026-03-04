<?php

namespace App\Http\Requests\User\Rating;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:product,delivery,basket,schedule_basket,shop,recipe,brand,order'],
            'rateable_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
