<?php

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $adminId = $this->route('admin');
        return [
            'name'              => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:admins,email,' . $adminId],
            'password' => ['required'],
            'is_active'            => 'nullable|boolean',
            'type' => 'nullable|in:square,circle,color',
            'roles' => 'nullable|array',
            'roles.*.id' => 'required|exists:roles,id'

        ];
    }
}
