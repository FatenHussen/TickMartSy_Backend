<?php

namespace App\Http\Requests\Admin\Banner;

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
            'title.ar'              => 'nullable|string|max:255',
            'title.en'              => 'nullable|string|max:255',
            'description.ar'       => 'nullable|string',
            'description.en'       => 'nullable|string',
            'image'                 => 'required|image|mimes:jpeg,png,jpg,gif,webp',
            // 'is_active'            => 'nullable|boolean',
            'link' => 'nullable|string|url',
        ];
    }
}
