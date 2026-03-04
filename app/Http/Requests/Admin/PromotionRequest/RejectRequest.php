<?php

namespace App\Http\Requests\Admin\PromotionRequest;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_notes' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_notes.required' => 'يجب إدخال سبب الرفض',
            'admin_notes.string' => 'سبب الرفض يجب أن يكون نص',
            'admin_notes.max' => 'سبب الرفض يجب ألا يتجاوز 1000 حرف',
        ];
    }
}
