<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'ملف الإكسل مطلوب',
            'file.file' => 'يجب رفع ملف صالح',
            'file.mimes' => 'يجب أن يكون الملف من نوع Excel (xlsx أو xls)',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 10 ميغابايت',
        ];
    }
}
