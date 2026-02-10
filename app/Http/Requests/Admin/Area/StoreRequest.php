<?php

namespace App\Http\Requests\Admin\Area;

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
            'name.ar'              => 'required|string|max:255',
            'name.en'              => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'is_active'            => 'nullable|boolean',
            'base_fee' => 'required|numeric',
            'lat' => 'required',
            'lng' => 'required'
        ];
    }
}
