<?php

namespace App\Http\Requests\Admin\Schedule;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        $prepared = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $prepared[$locale] = $data['name'][$locale];
            }
        }
        $this->merge(['name' => $prepared]);
    }

    public function rules(): array
    {
        $rules = [
            'interval_days' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ];

        // Add locale-specific validation for name
        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }
}
