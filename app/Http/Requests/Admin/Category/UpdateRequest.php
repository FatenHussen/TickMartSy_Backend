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

    public function rules(): array
    {
        $categoryId = $this->route('categories.update')?->id
            ?? $this->route('categories.update');

        $locales = Language::active()->pluck('code')->toArray();

        $rules = [
            'icon' => 'nullable|file',
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::notIn([$categoryId]),
            ],
        ];

        foreach ($locales as $locale) {
            $rules["name.{$locale}"] = 'nullable|string|max:255';
            $rules["description.{$locale}"] = 'nullable|string';
        }

        return $rules;
    }

}
