<?php

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\Concerns\ValidatesAdminRecordCityScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    use ValidatesAdminRecordCityScope;

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForAdminRecordCityScope($validator);
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
            'roles.*.id' => 'required|exists:roles,id',
            'city_ids' => 'nullable|array',
            'city_ids.*' => 'integer|exists:cities,id',
        ];
    }
}
