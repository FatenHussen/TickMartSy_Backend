<?php

namespace App\Http\Requests\User\Address;

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
            'label' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'street_name' => 'required|string|max:255',
            'nearest_landmark' => 'nullable|string|max:255',
            'building_number' => 'nullable|string|max:50',
            'floor_apartment' => 'nullable|string|max:50',
            'contact_phone' => 'required|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_default' => 'boolean',
        ];
    }
}
