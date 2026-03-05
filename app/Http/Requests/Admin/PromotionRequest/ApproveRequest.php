<?php

namespace App\Http\Requests\Admin\PromotionRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_notes.string' => 'ملاحظات الإدارة يجب أن تكون نص',
            'admin_notes.max' => 'ملاحظات الإدارة يجب ألا تتجاوز 1000 حرف',
        ];
    }
}
