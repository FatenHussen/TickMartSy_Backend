<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:2|max:100',
            'address' => 'nullable|string|max:255',
            'rate_per_order' => 'nullable|numeric|min:0',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'vehicle_type' => 'nullable|in:car,motorcycle,bicycle',
            'vehicle_number' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a string',
            'name.min' => 'Name must be at least 2 characters',
            'name.max' => 'Name must not exceed 100 characters',
            'address.string' => 'Address must be a string',
            'address.max' => 'Address must not exceed 255 characters',
            'rate_per_order.numeric' => 'Rate per order must be a number',
            'rate_per_order.min' => 'Rate per order must be at least 0',
            'image.file' => 'Image must be a file',
            'image.mimes' => 'Image must be of type: jpg, jpeg, png, webp',
            'image.max' => 'Image must not exceed 2MB',
            'vehicle_type.in' => 'Vehicle type must be one of: car, motorcycle, bicycle',
            'vehicle_number.string' => 'Vehicle number must be a string',
            'vehicle_number.max' => 'Vehicle number must not exceed 50 characters',
        ];
    }
}