<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $couponId = $this->route('coupon');
        // لو تستخدم Route Model Binding:
        // $couponId = $this->coupon->id;

        return [
            'name' => ['sometimes', 'array'],
            'name.*' => ['required', 'string', 'max:255'],

            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],

            'discount_type' => [
                'sometimes',
                Rule::in(['percentage', 'fixed']),
            ],

            'discount_value' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'start_at' => [
                'sometimes',
                'date',
            ],

            'end_at' => [
                'sometimes',
                'date',
                'after:start_at',
            ],

            'max_uses' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'governorate_id' => [
                'nullable',
                'exists:governorates,id',
            ],

            'city_id' => [
                'nullable',
                Rule::exists('cities', 'id')->when(
                    $this->filled('governorate_id'),
                    fn($query) => $query->where('governorate_id', $this->governorate_id)
                ),
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            // Relations
            'products' => ['sometimes', 'array'],
            'products.*' => ['exists:products,id'],

            'categories' => ['sometimes', 'array'],
            'categories.*' => ['exists:categories,id'],

            'vendors' => ['sometimes', 'array'],
            'vendors.*' => ['exists:vendors,id'],

            'shops' => ['sometimes', 'array'],
            'shops.*' => ['exists:shops,id'],
        ];
    }
}
