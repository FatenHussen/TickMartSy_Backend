<?php

namespace App\Http\Requests\Admin\PromotionRequest;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(PromotionStatus::class)],
            'type' => ['nullable', Rule::enum(PromotionType::class)],
            'vendor_id' => 'nullable|exists:vendors,id',
            'shop_id' => 'nullable|exists:shops,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
