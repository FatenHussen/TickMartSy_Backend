<?php

namespace App\Http\Requests\Admin\Driver;

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
            'name'              => 'required|string|max:255',
            'phone' => ['required', 'unique:drivers,phone'],
            'email' => ['nullable', 'email', 'unique:drivers,email'],
            'password' => ['required', 'min:8'],
            'is_active'            => 'nullable|boolean',
            'address' => 'nullable|string',
            'status' => 'nullable|in:available,busy,inactive',
            'area_ids' => 'nullable|array',
            'area_ids.*.id' => 'required|integer|exists:areas,id',
            'rate_per_order' => 'required',
            'vehicle_type' => 'required',
            'vehicle_number' => 'required',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',

        ];
    }
}
