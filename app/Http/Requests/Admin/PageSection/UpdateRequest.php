<?php

namespace App\Http\Requests\Admin\PageSection;

use App\Enums\VariantSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'array'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],

            'section_id' => ['nullable', 'integer', 'exists:sections,id'],

            'page_id' => ['nullable', 'integer', 'exists:pages,id'],

            'display_type_id' => ['prohibited'],

            'position' => ['nullable', 'in:before,after'],
            'order' => ['nullable', 'integer'],
            'variant' => ['nullable', Rule::in(VariantSection::values())],

            'background_color' => ['nullable', 'string', 'max:50'],
            'background_card_color' => ['nullable', 'string', 'max:50'],

            'filters' => ['nullable', 'array'],
            'filters.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.min_price' => ['nullable', 'integer', 'min:1'],
            'filters.max_price' => ['nullable', 'integer', 'min:1'],
            'filters.price' => ['nullable', 'integer', 'min:1'],

            'show_when' => ['nullable', 'array'],
            'show_when.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.en.required' => 'The English name is required.',
            'name.ar.required' => 'The Arabic name is required.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Section not found.',
            'page_ids.required' => 'Pages are required.',
            'page_ids.*.item_id.exists' => 'Page item not found.',
        ];
    }
}
