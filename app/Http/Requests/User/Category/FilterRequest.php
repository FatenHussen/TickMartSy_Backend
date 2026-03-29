<?php

namespace App\Http\Requests\User\Category;

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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('parent_id')) {
            $this->merge([
                'parent_id' => null,
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
            'parent_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'search' => 'nullable|string',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'type' => 'nullable|in:new,most_popular,top_rated',
            'sort_by' => 'nullable|in:newest,oldest',
        ];
    }
}
