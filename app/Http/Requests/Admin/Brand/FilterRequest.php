<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $countryId = $this->input('country_id');
        $originCountryId = $this->input('origin_country_id');

        $subCategoryId = $this->input('sub_category_id');

        if ($subCategoryId === null || $subCategoryId === '') {
            $subCategoryId = $this->input('child_category_id');
        }

        if ($subCategoryId === null || $subCategoryId === '') {
            $subCategoryId = $this->input('second_category_id');
        }

        if (($originCountryId === null || $originCountryId === '') && ($countryId !== null && $countryId !== '')) {
            $this->merge([
                'origin_country_id' => $countryId,
            ]);
        }

        if ($subCategoryId !== null && $subCategoryId !== '') {
            $this->merge([
                'sub_category_id' => $subCategoryId,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'category_id' => 'nullable|integer|exists:categories,id',
            'sub_category_id' => 'nullable|integer|exists:categories,id',
            'origin_country_id' => 'nullable|integer|exists:countries,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ];
    }
}
