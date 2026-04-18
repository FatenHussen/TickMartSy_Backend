<?php

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\Concerns\ValidatesAdminRecordCityScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
{
    use ValidatesAdminRecordCityScope;

    public function withValidator(Validator $validator): void
    {
        $this->withValidatorForAdminRecordCityScope($validator);
    }

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
            'phone' => ['required', 'string', 'unique:admins,phone,' . $adminId],
            'email' => ['required', 'email', 'unique:admins,email,' . $adminId],
            'password' => ['nullable'],
            'is_active'            => 'nullable|boolean',
            'type' => 'nullable|in:square,circle,color',
            'roles' => 'nullable|array',
            'roles.*.id' => 'required|exists:roles,id',
            'city_ids' => 'nullable|array',
            'city_ids.*' => 'integer|exists:cities,id',
        ];
    }
}
