<?php

namespace App\Http\Requests\Admin\Faq;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question.en' => 'required|string',
            'question.ar' => 'required|string',
            'answer.en'  => 'required|string',
            'answer.ar'  => 'required|string',
            'type'   => 'required|in:orders,delivery,payments,account,stores&drivers,other',
        ];
    }
}
