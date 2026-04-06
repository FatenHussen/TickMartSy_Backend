<?php

namespace App\Http\Requests\Admin\Brand;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $locales = Language::active()->pluck('code')->toArray();

        $rules = [
            'image'          => 'nullable|file',
            'governorate_id' => 'nullable|integer|exists:governorates,id',
            'city_id'        => 'nullable|integer|exists:cities,id',
            'category_id'    => 'nullable|integer|exists:categories,id',
        ];

        foreach ($locales as $locale) {
            $rules["name.{$locale}"] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
