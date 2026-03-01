<?php

namespace App\Http\Requests\Admin\Schedule;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

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

        // Prepare translatable name field
        if (isset($data['name'])) {
            $prepared = [];
            foreach ($this->locales as $locale) {
                if (isset($data['name'][$locale])) {
                    $prepared[$locale] = $data['name'][$locale];
                } else {
                    $prepared[$locale] = null;
                }
            }
            $this->merge(['name' => $prepared]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'interval_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ];

        // Add locale-specific validation for name
        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
