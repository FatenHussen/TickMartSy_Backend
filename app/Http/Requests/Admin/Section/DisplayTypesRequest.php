<?php

namespace App\Http\Requests\Admin\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisplayTypesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'manual_model' => ['required', 'string', Rule::in(array_keys(config('section_items')))],
            'page_id' => ['required', 'integer', 'exists:pages,id'],
        ];
    }
}
