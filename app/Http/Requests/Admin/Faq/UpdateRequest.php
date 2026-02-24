<?php

namespace App\Http\Requests\Admin\Faq;

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
            'question.en' => 'nullable|string',
            'question.ar' => 'nullable|string',
            'answer.en'  => 'nullable|string',
            'answer.ar'  => 'nullable|string',
            'type'   => 'nullable|in:orders,delivery,payments,account,stores&drivers,other',
        ];
    }
}
