<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Log;

class VerifyUpdateRequest extends BaseRequest
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
            'phone' => 'nullable|string|required_without:email',
            'email' => 'nullable|string|required_without:phone',
            'code' => 'required|string',
        ];
    }

    public function messages(): array
    {
        $locale = $this->getLocale();

        if ($locale === 'ar') {
            return $this->arabicMessages();
        }

        return $this->englishMessages();
    }

    private function arabicMessages(): array
    {
        return [
            'phone.string' => 'الهاتف يجب أن يكون نصاً.',
            'phone.required_without' => 'الهاتف مطلوب عندما البريد الإلكتروني غير مدخل.',

            'email.string' => 'البريد الإلكتروني يجب أن يكون نصاً.',
            'email.required_without' => 'البريد الإلكتروني مطلوب عندما الهاتف غير مدخل.',

            'code.required' => 'الكود مطلوب.',
            'code.string' => 'الكود يجب أن يكون نصاً.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'phone.string' => 'The phone must be a string.',
            'phone.required_without' => 'The phone field is required when email is not present.',

            'email.string' => 'The email must be a string.',
            'email.required_without' => 'The email field is required when phone is not present.',

            'code.required' => 'The code field is required.',
            'code.string' => 'The code must be a string.',
        ];
    }
    protected function prepareForValidation()
    {
        Log::info('Incoming VerifyPasswordRequest data:', $this->all());

        if ($this->has('phone')) {
            $cleanPhone = ltrim($this->input('phone'), '+');
            $this->merge([
                'phone' => $cleanPhone,
            ]);
        }
    }
}
