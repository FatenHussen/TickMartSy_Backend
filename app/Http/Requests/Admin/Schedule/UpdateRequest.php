<?php

namespace App\Http\Requests\Admin\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;
use Illuminate\Http\UploadedFile;

class UpdateRequest extends FormRequest
{
    protected array $locales = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->locales = Language::active()->pluck('code')->toArray();
        $data = $this->all();

        if (isset($data['name'])) {
            $prepared = [];
            foreach ($this->locales as $locale) {
                $prepared[$locale] = $data['name'][$locale] ?? null;
            }
            $this->merge(['name' => $prepared]);
        }

        if (isset($data['description'])) {
            $prepared = [];
            foreach ($this->locales as $locale) {
                $prepared[$locale] = $data['description'][$locale] ?? null;
            }
            $this->merge(['description' => $prepared]);
        }

        if ($this->file('images') instanceof UploadedFile) {
            $this->merge([
                'images' => [$this->file('images')],
            ]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'interval_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer',
            'badges' => 'nullable|array',
            'badges.*.id' => 'required|integer|exists:badges,id',
            'badges.*.position' => 'nullable|in:top,bottom',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'nullable|string|max:255';
            $rules["description.$locale"] = 'nullable|string|max:2000';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'interval_days.integer' => 'عدد الأيام يجب أن يكون رقم صحيح',
            'interval_days.min' => 'عدد الأيام يجب أن يكون على الأقل 1',
            'discount_type.in' => 'نوع الخصم يجب أن يكون percentage أو fixed',
            'discount_value.numeric' => 'قيمة الخصم يجب أن تكون رقم',
            'discount_value.min' => 'قيمة الخصم يجب أن تكون أكبر من أو تساوي 0',
        ];
    }
}
