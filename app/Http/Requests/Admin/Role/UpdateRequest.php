<?php

namespace App\Http\Requests\Admin\Role;

use App\Rules\UniqueRole;
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
        return [
            'name' => ['required', new UniqueRole(
                $this->guard_name ?? 'admin',
                $this->route('role') ?? null
            )],
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
