<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UserRegisterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'phone' => 'nullable|string|unique:users,phone|required_without:email|regex:/^\\d+$/',
            'email' => 'nullable|string|unique:users,email|required_without:phone|regex:/@/',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
            'city_id' =>  'required|exists:cities,id',
            'governorate_id' =>  'required|exists:governorates,id',

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
            'phone.unique' => 'الهاتف مسجل مسبقاً.',
            'phone.required_without' => 'الهاتف مطلوب عندما البريد الإلكتروني غير مدخل.',
            'phone.regex' => 'صيغة الهاتف غير صحيحة.',

            'email.string' => 'البريد الإلكتروني يجب أن يكون نصاً.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً.',
            'email.required_without' => 'البريد الإلكتروني مطلوب عندما الهاتف غير مدخل.',
            'email.regex' => 'صيغة البريد الإلكتروني غير صحيحة.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'كلمة المرور يجب أن تكون نصية.',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف.',
            'password.regex' => 'كلمة المرور يجب أن تحتوي على حرف صغير، حرف كبير، رقم، ورمز خاص.',

            'fcm_token.required' => 'رمز FCM مطلوب.',
            'fcm_token.exists' => 'رمز FCM غير صالح.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'phone.string' => 'The phone must be a string.',
            'phone.unique' => 'The phone has already been taken.',
            'phone.required_without' => 'The phone field is required when email is not present.',
            'phone.regex' => 'The phone format is invalid.',

            'email.string' => 'The email must be a string.',
            'email.unique' => 'The email has already been taken.',
            'email.required_without' => 'The email field is required when phone is not present.',
            'email.regex' => 'The email format is invalid.',

            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character.',

            'fcm_token.required' => 'The FCM token field is required.',
            'fcm_token.exists' => 'The FCM token is invalid.',
        ];
    }
}
