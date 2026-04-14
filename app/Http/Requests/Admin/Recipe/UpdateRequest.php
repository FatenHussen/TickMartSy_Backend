<?php

namespace App\Http\Requests\Admin\Recipe;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'name' => ['nullable', 'array'],
            'name.*' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.*' => ['required', 'string'],

            'image' => ['nullable', 'image'],
            'video_url' => ['nullable', 'url'],
            'video_title' => ['nullable', 'array'],
            'video_title.*' => ['required', 'string', 'max:255'],
            'video_desc' => ['nullable', 'array'],
            'video_desc.*' => ['required', 'string'],

            'discount' => ['nullable', 'numeric', 'min:1'],
            // 'orders_count' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],

            'serves' => 'nullable',
            'prepare_time' => 'nullable',


            'delivery_price' => ['nullable', 'numeric', 'min:0'],

            /* =======================
             * Items
             * ======================= */
            'items' => ['nullable', 'array', 'min:1'],

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
            'steps' => ['nullable', 'array', 'min:1'],

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


            'badges'          => 'nullable|array',
            'badges.*' => 'integer|exists:badges,id',
        ];
    }
}
