<?php

namespace App\Http\Requests\User\Basket;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_schedule' => 'nullable|boolean',
            'category_id' => 'nullable|integer|exists:categories,id',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'rating_min' => 'nullable|numeric|min:0|max:5',
            'items_count_min' => 'nullable|integer|min:0',
            'items_count_max' => 'nullable|integer|min:0',
            'type' => 'nullable|in:new,best_selling,top_rated',
            'sort_by' => 'nullable|in:price_desc,price_asc,newest,oldest,rating_desc,rating_asc',
        ];
    }
}
