<?php

namespace App\Http\Requests\Admin\PageSection;

use Illuminate\Foundation\Http\FormRequest;

class ReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.id' => ['required', 'integer', 'distinct', 'exists:page_sections,id'],
            'sections.*.order' => ['required', 'integer', 'min:1'],
            'sections.*.position' => ['required', 'in:before,after'],
        ];
    }
}
