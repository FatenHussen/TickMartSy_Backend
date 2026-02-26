<?php

namespace App\Http\Requests\Admin\Recipe;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /* =======================
             * Recipe
             * ======================= */
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],

            'description' => ['required', 'array'],
            'description.*' => ['required', 'string'],

            'image' => ['required', 'image'],
            'video_url' => ['required', 'url'],

            'discount' => ['required', 'numeric', 'min:1'],
            // 'orders_count' => ['nullable', 'integer', 'min:0'],

            'delivery_price' => ['required', 'numeric', 'min:0'],

            /* =======================
             * Items
             * ======================= */
            'items' => ['required', 'array', 'min:1'],

            'items.*.shop_product_variant_id' => [
                'required',
                'exists:shop_product_variants,id'
            ],

            'items.*.switchable_category_id' => [
                'nullable',
                'exists:categories,id'
            ],

            'items.*.quantity' => [
                'nullable',
                'integer',
                'min:1'
            ],

            'items.*.is_required' => [
                'nullable',
                'boolean'
            ],

            'items.*.min_quantity' => [
                'nullable',
                'integer',
                'min:1'
            ],

            'items.*.max_quantity' => [
                'nullable',
                'integer',
                'gte:items.*.min_quantity'
            ],

            /* =======================
             * Steps
             * ======================= */
            'steps' => ['required', 'array', 'min:1'],

            'steps.*.step_number' => [
                'required',
                'integer',
                'min:1'
            ],

            'steps.*.heat_level' => [
                'nullable',
                'array'
            ],
            'steps.*.heat_level.*' => [
                'nullable',
                'string'
            ],

            'steps.*.time_minutes' => [
                'nullable',
                'array'
            ],
            'steps.*.time_minutes.*' => [
                'nullable',
                'string'
            ],

            'steps.*.instruction' => [
                'required',
                'array'
            ],
            'steps.*.instruction.*' => [
                'required',
                'string'
            ],
        ];
    }
}
