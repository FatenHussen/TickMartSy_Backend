<?php

namespace App\Http\Requests\User\Complaint;

use App\Enums\ComplaintType;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:5'],
            'order_id' => ['required', 'exists:orders,id'],
            'images'   => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png'],
            'type' => [
                'required',
                'in:' . implode(',', array_column(ComplaintType::cases(), 'value')),
            ],
        ];
    }
}
