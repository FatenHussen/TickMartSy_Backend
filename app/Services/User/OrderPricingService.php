<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Models\Basket;
use App\Models\Coupon;
use App\Models\Recipe;
use App\Models\ShopProductVariant;
use App\Models\User;

class OrderPricingService
{
    public function calculate(array $data): array
    {
        $user = auth('user')->user() ?? User::find(1);

        [$basketDiscount, $deliveryPrice] = $this->resolveBasketAndDelivery($data, $user);
        [$subtotal, $subtotalAfterDiscount, $quantity, $items] = $this->buildItems($data);
        [$couponCode, $couponDiscount] = $this->applyCoupon($items, $data);

        // إذا تم تطبيق كوبون → نلغي خصم السلة
        if ($couponCode) {
            $basketDiscount = 0;
        }

        $basketDiscountAmount = $subtotalAfterDiscount * ($basketDiscount / 100);
        $total = $subtotalAfterDiscount - ($basketDiscountAmount + $couponDiscount);

        return [
            'delivery_price'  => round($deliveryPrice, 2),
            'subtotal'        => round($subtotal, 2),
            'total_quantity'  => $quantity,
            'basket_discount' => $basketDiscount,
            'coupon_code'     => $couponCode,
            'coupon_discount' => round($couponDiscount, 2),
            'total'           => round($total, 2),
            'items'           => $items,
        ];
    }

    /* ============================ */

    protected function resolveBasketAndDelivery(array $data, User $user): array
    {
        $cartType = $data['cart_type'] ?? CartType::DEFAULT->value;

        return match ($cartType) {
            CartType::RECIPE->value => [
                Recipe::findOrFail($data['recipe_id'])->discount,
                Recipe::findOrFail($data['recipe_id'])->delivery_price
            ],

            CartType::ADMIN_CART->value => [
                Basket::findOrFail($data['admin_basket_id'])->discount,
                Basket::findOrFail($data['admin_basket_id'])->delivery_price
            ],

            CartType::SCHEDULE_ADMIN_CART->value => function() use ($data) {
                $basketId = $data['admin_schedule_basket_id'] ?? $data['admin_basket_id'];
                $basket = Basket::findOrFail($basketId);

                $basketDiscount = $basket->discount;

                // Check if user selected a specific schedule
                if (!empty($data['basket_schedule_id'])) {
                    $basketSchedule = $basket->schedules()
                        ->where('id', $data['basket_schedule_id'])
                        ->where('is_active', true)
                        ->first();

                    if ($basketSchedule && $basketSchedule->discount_value > 0) {
                        $basketDiscount = $basketSchedule->discount_value;
                    }
                }

                return [$basketDiscount, $basket->delivery_price];
            },

            default => [
                0,
                CalculateDeliveryPriceService::handle(
                    user: $user,
                    items: collect($data['items'])->pluck('shop_product_variant_id')->toArray(),
                    addressId: $data['address_id']
                )
            ],
        };
    }

    protected function buildItems(array $data): array
    {
        $subtotal = 0;
        $subtotalAfterDiscount = 0;
        $quantity = 0;
        $items = collect();

        foreach ($data['items'] as $item) {
            $variant = ShopProductVariant::with('productVariant.product')
                ->findOrFail($item['shop_product_variant_id']);

            $product = $variant->productVariant->product;
            $price = $variant->price;
            $qty = $item['quantity'];

            $productDiscount =
                ($data['cart_type'] ?? CartType::DEFAULT->value) === CartType::DEFAULT->value
                ? $product->discount
                : 0;

            $priceAfterDiscount = $price * (1 - $productDiscount / 100);

            $subtotal += $price * $qty;
            $subtotalAfterDiscount += $priceAfterDiscount * $qty;
            $quantity += $qty;

            $items->push([
                'shop_product_variant_id' => $variant->id,
                'quantity' => $qty,
                'price_after_discount' => $priceAfterDiscount,
            ]);
        }

        return [$subtotal, $subtotalAfterDiscount, $quantity, $items];
    }

    protected function applyCoupon($items, array $data): array
    {
        if (empty($data['coupon'])) {
            return [null, 0];
        }

        $coupon = Coupon::where('code', $data['coupon'])->first();
        if (!$coupon || !$coupon->isValid()) {
            return [null, 0];
        }

        $cartType = $data['cart_type'] ?? CartType::DEFAULT->value;
        if (
            $cartType !== CartType::DEFAULT->value &&
            !config('settings.allow_coupons_on_basket')
        ) {
            return [null, 0];
        }

        $eligibleSubtotal = $items->sum(
            fn($i) => $i['price_after_discount'] * $i['quantity']
        );

        $discount = $coupon->discount_type === 'percent'
            ? $eligibleSubtotal * ($coupon->discount_value / 100)
            : min($coupon->discount_value, $eligibleSubtotal);

        return [$coupon->code, $discount];
    }
}
