<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

class StoreRequest extends FormRequest
{
   
    protected array $locales = [];

    public function __construct()
    {
        parent::__construct();

        $this->locales = $this->getAvailableLocales();
    }

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $rules = [
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.{$locale}"] = [
                'required',
                'string',
                'max:255',
            ];

            $rules["description.{$locale}"] = 'nullable|string';
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->locales as $locale) {
            $localeName = $this->getLocaleName($locale);

            $attributes["name.{$locale}"] = "الاسم ({$localeName})";
            $attributes["description.{$locale}"] = "الوصف ({$localeName})";
        }

        return $attributes;
    }

    public function messages(): array
    {
        $messages = [];

        foreach ($this->locales as $locale) {
            $localeName = $this->getLocaleName($locale);

            $messages["name.{$locale}.required"] = "الاسم باللغة {$localeName} مطلوب";
            $messages["name.{$locale}.max"] = "الاسم باللغة {$localeName} يجب ألا يتجاوز 255 حرف";
        }

        return $messages;
    }

    protected function getAvailableLocales(): array
    {
        if (config('locales.locales')) {
            return array_keys(config('locales.locales'));
        }
        if (class_exists('\App\Models\Language')) {
            return \App\Models\Language::active()
                ->pluck('code')
                ->toArray();
        }
        return config('app.available_locales', ['ar', 'en']);
    }

    protected function getLocaleName(string $locale): string
    {
        $localeConfig = config("locales.locales.{$locale}");

        if ($localeConfig && isset($localeConfig['name'])) {
            return $localeConfig['name'];
        }

        $defaultNames = [
            'ar' => 'العربية',
            'en' => 'الإنجليزية',
        ];

        return $defaultNames[$locale] ?? $locale;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        $translations = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $translations[$locale]['name'] = $data['name'][$locale];
            }
            if (isset($data['description'][$locale])) {
                $translations[$locale]['description'] = $data['description'][$locale];
            }
        }

        $this->merge([
            'name' => $translations,
            'description' => $translations,
        ]);
    }
}
