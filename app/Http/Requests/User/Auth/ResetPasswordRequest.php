<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class ResetPasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|same:new_password',
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
            'new_password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'new_password.string' => 'كلمة المرور الجديدة يجب أن تكون نصاً.',
            'new_password.min' => 'كلمة المرور الجديدة يجب أن تكون على الأقل 8 أحرف.',

            'new_password_confirmation.required' => 'تأكيد كلمة المرور الجديدة مطلوب.',
            'new_password_confirmation.same' => 'تأكيد كلمة المرور الجديدة يجب أن يتطابق مع كلمة المرور الجديدة.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'new_password.required' => 'The new password field is required.',
            'new_password.string' => 'The new password must be a string.',
            'new_password.min' => 'The new password must be at least 8 characters.',

            'new_password_confirmation.required' => 'The new password confirmation field is required.',
            'new_password_confirmation.same' => 'The new password confirmation must match the new password.',
        ];
    }
}
