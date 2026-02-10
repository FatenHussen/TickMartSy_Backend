<?php

namespace App\Http\Requests\User\Complaint;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
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
            'status'   => [
                'nullable',
                'in:' . implode(',', array_column(ComplaintStatus::cases(), 'value')),
            ],

            'order_id' => ['nullable', 'integer'],
            'from'     => ['nullable', 'date'],
            'to'       => ['nullable', 'date'],
        ];
    }
}
