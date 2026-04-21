<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;

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

        $name = [];

        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $name[$locale] = $data['name'][$locale];
            }
        }

        // Convert empty string to null for parent_id
        $parentId = $this->parent_id;
        if ($parentId === '') {
            $parentId = null;
        }

        $this->merge([
            'name' => $name,
            'parent_id' => $parentId,
        ]);
    }


    public function rules(): array
    {
        $rules = [
            'icon' => 'nullable|file',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_restaurant' => 'nullable|boolean',
        ];

        foreach ($this->locales as $locale) {
            $rules["name.{$locale}"] = 'required|string|max:255';
        }

        return $rules;
    }


    protected function getLocaleName(string $locale): string
    {
        return match ($locale) {
            'ar' => 'العربية',
            'en' => 'الإنجليزية',
            default => $locale,
        };
    }
}
