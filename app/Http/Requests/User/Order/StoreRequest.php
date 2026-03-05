<?php

namespace App\Http\Requests\User\Order;

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
            'address_id' => ['required', 'exists:user_addresses,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'cart_type' => ['nullable', 'string', 'in:default,recipe,admin_cart,schedule_admin_cart'],
            'is_instant_delivery' => ['required', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.shop_product_variant_id' => [
                'required',
                'exists:shop_product_variants,id'
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'recipe_id' => 'nullable|exists:recipes,id',
            'admin_basket_id' => 'nullable|exists:baskets,id',
            'admin_schedule_basket_id' => 'nullable|exists:baskets,id',
            'basket_schedule_id' => 'nullable|exists:basket_schedules,id',
            'coupon' => 'nullable|string',
            'affiliate_id' => 'nullable|string',

            // Point exchanges
            'point_coupon_exchange_id' => 'nullable|integer|exists:point_exchanges,id',
            'point_free_delivery_exchange_id' => 'nullable|integer|exists:point_exchanges,id',

            // Subscription benefits (user choice)
            'use_subscription_discount' => 'nullable|boolean',
            'use_subscription_free_delivery' => 'nullable|boolean',
            //add schedule
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Validation: Only ONE discount source allowed
            $discountSources = 0;
            if (!empty($data['coupon'])) $discountSources++;
            if (!empty($data['point_coupon_exchange_id'])) $discountSources++;
            if (!empty($data['use_subscription_discount'])) $discountSources++;

            if ($discountSources > 1) {
                $validator->errors()->add('discount_conflict', 'يمكن استخدام مصدر خصم واحد فقط: كوبون أو نقاط أو باقة');
            }

            // Validation: Only ONE free delivery source allowed
            $freeDeliverySources = 0;
            if (!empty($data['point_free_delivery_exchange_id'])) $freeDeliverySources++;
            if (!empty($data['use_subscription_free_delivery'])) $freeDeliverySources++;

            if ($freeDeliverySources > 1) {
                $validator->errors()->add('free_delivery_conflict', 'يمكن استخدام مصدر توصيل مجاني واحد فقط: نقاط أو باقة');
            }
        });
    }
}
