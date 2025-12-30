<?php

namespace App\Http\Requests\Admin\Role;

use App\Rules\UniqueRole;
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
            'name' => ['required', new UniqueRole($this->guard_name ?? 'admin')],
            'guard_name' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*.id' => 'required|exists:permissions,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'guard_name' => 'admin',
        ]);
    }
}
