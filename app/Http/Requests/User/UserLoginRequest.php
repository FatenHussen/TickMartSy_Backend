<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UserLoginRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'phone' => 'nullable|string|exists:users,phone|required_without:email|regex:/^\\d+$/',
            'email' => 'nullable|string|exists:users,email|required_without:phone|regex:/@/',
            'password' => 'required|string|min:8',

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
            'phone.exists' => 'الهاتف غير مسجل في النظام.',
            'phone.required_without' => 'الهاتف مطلوب عندما البريد الإلكتروني غير مدخل.',
            'phone.regex' => 'صيغة الهاتف غير صحيحة.',

            'email.string' => 'البريد الإلكتروني يجب أن يكون نصاً.',
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام.',
            'email.required_without' => 'البريد الإلكتروني مطلوب عندما الهاتف غير مدخل.',
            'email.regex' => 'صيغة البريد الإلكتروني غير صحيحة.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'كلمة المرور يجب أن تكون نصية.',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف.',

            'fcm_token.string' => 'رمز FCM يجب أن يكون نصياً.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'phone.string' => 'The phone must be a string.',
            'phone.exists' => 'The phone number is not registered.',
            'phone.required_without' => 'The phone field is required when email is not present.',
            'phone.regex' => 'The phone format is invalid.',

            'email.string' => 'The email must be a string.',
            'email.exists' => 'The email address is not registered.',
            'email.required_without' => 'The email field is required when phone is not present.',
            'email.regex' => 'The email format is invalid.',

            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',

            'fcm_token.string' => 'The FCM token must be a string.',
        ];
    }
}