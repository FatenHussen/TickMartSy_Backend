<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UpdateEmailRequest extends BaseRequest
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
            'email' => 'required|email|regex:/@/'
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
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح.',
            'email.regex' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.regex' => 'The email format is invalid.',
        ];
    }
}
