<?php

namespace App\Http\Requests\Admin\Admin;

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
            'name'              => 'required|string',
            'phone' => ['required', 'string', 'unique:admins,phone'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'min:8'],
            'is_active'            => 'nullable|boolean',
            'type' => 'nullable|in:square,circle,color',
            'roles' => 'nullable|array',
            'roles.*.id' => 'required|exists:roles,id'
        ];
    }
}
