<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Language;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Convert empty string to null for parent_id
        if ($this->has('parent_id') && $this->parent_id === '') {
            $this->merge(['parent_id' => null]);
        }
    }

    public function rules(): array
    {
        $routeCategory = $this->route('categories.update');
        $categoryId = is_object($routeCategory) ? ($routeCategory->id ?? null) : $routeCategory;

        $locales = Language::active()->pluck('code')->toArray();

        $rules = [
            'icon' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
            'parent_id' => [
                'nullable',
                //'integer',
                'exists:categories,id',
                Rule::notIn([$categoryId]),
            ],
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_restaurant' => 'nullable|boolean',
        ];

        foreach ($locales as $locale) {
            $rules["name.{$locale}"] = 'nullable|string|max:255';
        }

        return $rules;
    }

}
