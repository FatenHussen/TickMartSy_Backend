<?php

namespace App\Http\Requests\Admin\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $type = $this->input('manual_model'); // 'banner'
        $typeConfig = config("section_items.$type");

        if ($typeConfig) {
            $itemType = $typeConfig['item_type'];

            $itemIds = $this->input('item_ids', []);
            foreach ($itemIds as &$item) {
                $item['item_type'] = $itemType;
            }

            $this->merge(['item_ids' => $itemIds]);
        }
    }

    public function rules(): array
    {
        $allowedTypes = array_keys(config('section_items'));

        return [
            'name' => ['nullable', 'array'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],

            'manual_model' => ['required', 'string', Rule::in($allowedTypes)],

            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*.item_type' => ['required', 'string'],
            'item_ids.*.item_id' => ['required', 'integer'],
            'item_ids.*.link' => ['nullable', 'string', 'max:255'],
            'item_ids.*.order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
