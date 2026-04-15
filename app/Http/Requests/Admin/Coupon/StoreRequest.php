<?php

namespace App\Http\Requests\Admin\Coupon;

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
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code',
            ],

            'discount_type' => [
                'required',
                Rule::in(['percentage', 'fixed']),
            ],

            'discount_value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'start_at' => [
                'required',
                'date',
                'after:now',
            ],

            'end_at' => [
                'required',
                'date',
                'after:start_at',
            ],

            'max_uses' => [
                'required',
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

            'affiliate_id' => [
                'nullable',
                'exists:users,affiliate_id',
            ],

            'is_active' => [
                'boolean',
            ],

            // Relations (اختياري)
            'products' => ['sometimes', 'array'],
            'products.*.id' => ['exists:products,id'],

            'categories' => ['sometimes', 'array'],
            'categories.*.id' => ['exists:categories,id'],

            'vendors' => ['sometimes', 'array'],
            'vendors.*.id' => ['exists:vendors,id'],

            'shops' => ['sometimes', 'array'],
            'shops.*.id' => ['exists:shops,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'end_at.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية',
            'code.unique' => 'كود الكوبون مستخدم من قبل',
        ];
    }
}
