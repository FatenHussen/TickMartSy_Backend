<?php

namespace App\Http\Requests\Admin\VendorSubscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date',
            'auto_renew' => 'boolean',
            'status' => 'sometimes|in:active,pending,expired,cancelled',
            'notes' => 'nullable|string',
        ];
    }
}
