<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UpdatePhoneRequest extends BaseRequest
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
            'phone' => 'required|string|regex:/^\\d+$/'
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
            'phone.required' => 'الهاتف مطلوب.',
            'phone.string' => 'الهاتف يجب أن يكون نصاً.',
            'phone.regex' => 'صيغة الهاتف غير صحيحة.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'phone.required' => 'The phone field is required.',
            'phone.string' => 'The phone must be a string.',
            'phone.regex' => 'The phone format is invalid.',
        ];
    }
}
