<?php

namespace App\Http\Requests\User\Auth;

use App\Http\Requests\BaseRequest;

class UpdateProfileRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:3',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp',
            'city_id' => 'nullable|exists:cities,id'
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
            'name.string' => 'الاسم يجب أن يكون نصاً.',
            'name.min' => 'الاسم يجب أن يكون على الأقل 3 أحرف.',

            'address.string' => 'العنوان يجب أن يكون نصاً.',
            'address.max' => 'العنوان يجب ألا يتجاوز 255 حرفاً.',

            'birthdate.date' => 'تاريخ الميلاد يجب أن يكون تاريخاً صالحاً.',
            'birthdate.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم.',

            'gender.in' => 'الجنس يجب أن يكون: male, female, other.',

            'image.file' => 'الصورة يجب أن تكون ملفاً.',
            'image.mimes' => 'نوع الصورة يجب أن يكون: jpg, jpeg, png, webp.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',

            'city_id.exists' => 'المدينة المحددة غير موجودة.',
        ];
    }

    private function englishMessages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters.',

            'address.string' => 'The address must be a string.',
            'address.max' => 'The address must not exceed 255 characters.',

            'birthdate.date' => 'The birthdate must be a valid date.',
            'birthdate.before' => 'The birthdate must be before today.',

            'gender.in' => 'The gender must be: male, female, other.',

            'image.file' => 'The image must be a file.',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, webp.',
            'image.max' => 'The image must not exceed 2MB.',

            'city_id.exists' => 'The selected city does not exist.',
        ];
    }
}
