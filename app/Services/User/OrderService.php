<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Http\Resources\Order\OneResource;
use App\Http\Resources\Order\AllResource;
use App\Models\AffiliateWalletTransaction;
use App\Models\Basket;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\Coupon;
use App\Services\BaseService;
use App\Services\User\CalculateDeliveryPriceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService extends BaseService
{

    public function __construct(Order $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;

        $this->searchableFields = ['total', 'total_quantity'];
        $this->sortableFields   = ['id', 'total', 'total_quantity'];
        $this->relations        = ['items'];
        $this->pagination       = true;
    }

    /** -----------------------------
     * Create Order with Basket + Coupon
     * ----------------------------- */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {

            $user = auth('user')->user() ?? User::find(1);

            $order = Order::create([
                'user_id'             => $user->id,
                'user_address_id'     => $data['address_id'],
                'cart_type'           => $data['cart_type'] ?? CartType::DEFAULT->value,
                'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
                'status'        => OrderStatus::PENDING->value,
            ]);

            // 1️⃣ Resolve basket & delivery
            [$basketDiscount, $deliveryPrice] = $this->resolveBasketAndDelivery($order, $data);

            // 2️⃣ Add items to order
            [$subtotalBeforeDiscount, $subtotalAfterProductDiscount, $totalQuantity, $orderItems] =
                $this->addItemsToOrder($order, $data);

            // 3️⃣ Apply coupon if provided
            [$couponCode, $couponDiscountAmount, $excludedItems, $coupon] =
                $this->applyCoupon(
                    $order,
                    $orderItems,
                    $data['coupon'] ?? null,
                    $basketDiscount
                );


            // ❗ إذا تم تطبيق كوبون → نلغي خصم السلة
            if ($couponCode !== null) {
                $basketDiscount = 0;
            }


            // 4️⃣ Calculate totals
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);

            $finalTotal = $subtotalAfterProductDiscount
                - ($basketDiscountAmount + $couponDiscountAmount);


            // =======================
            // Affiliate / Marketer
            // =======================

            // الافتراضي: من رابط
            $affiliateId = $data['affiliate_id'] ?? null;
            $affiliateSource = $affiliateId ? 'link' : null;

            // إذا الكوبون تابع لمسوق → هو الأقوى
            if ($coupon && $coupon->affiliate_id) {
                $affiliateId = $coupon->affiliate_id;
                $affiliateSource = 'coupon';
            }

            // نجيب نسبة العمولة
            $affiliateRate = null;

            if ($affiliateId) {
                $affiliate = User::where('affiliate_id', $affiliateId)->first();
                $affiliateRate = $affiliate?->affiliate_rate;
            }

            // =======================
            // Update Order
            // =======================

            $order->update([
                'delivery_price'   => $deliveryPrice,
                'subtotal'         => round($subtotalBeforeDiscount, 2),
                'total_quantity'   => $totalQuantity,
                'basket_discount'  => $basketDiscount,
                'coupon_code'      => $couponCode,
                'coupon_discount'  => round($couponDiscountAmount, 2),
                'total'            => round($finalTotal, 2),

                // Affiliate data
                'affiliate_id'     => $affiliateId,
                'affiliate_rate'   => $affiliateRate,
                'affiliate_source' => $affiliateSource, // link | coupon | null
            ]);


            // =======================
            // تسجيل العمولة في المحفظة
            // =======================
            if ($affiliateId && $affiliateRate) {
                $commissionAmount = round($finalTotal * ($affiliateRate / 100), 2);

                AffiliateWalletTransaction::create([
                    'affiliate_id' => $affiliateId,
                    'type' => 'commission',
                    'amount' => $commissionAmount,
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
            }

            OrderCreated::dispatch($order);

            return new $this->resource($order->load('items'));
        });
    }


    public function preview(array $data)
    {
        return DB::transaction(function () use ($data) {

            // 1️⃣ Basket & delivery
            [$basketDiscount, $deliveryPrice] = $this->resolveBasketAndDelivery(null, $data);

            // 2️⃣ Items
            [
                $subtotalBeforeDiscount,
                $subtotalAfterProductDiscount,
                $totalQuantity,
                $orderItems
            ] = $this->addItemsToOrder(null, $data);

            // 3️⃣ Coupon info (default response)
            $couponInfo = [
                'provided' => !empty($data['coupon']),
                'valid' => false,
                'applied' => false,
                'code' => $data['coupon'] ?? null,
                'discount' => 0,
                'excluded_items' => [],
                'fail_reasons' => [],
            ];

            $couponDiscountAmount = 0;

            if (!empty($data['coupon'])) {

                $coupon = Coupon::where('code', $data['coupon'])->first();

                if (!$coupon) {
                    $couponInfo['fail_reasons'][] = 'Coupon not found';
                } elseif (!$coupon->isValid()) {
                    if (!$coupon->is_active) {
                        $couponInfo['fail_reasons'][] = 'Coupon is not active';
                    }
                    if ($coupon->isExpired()) {
                        $couponInfo['fail_reasons'][] = 'Coupon has expired';
                    }
                    if ($coupon->used_count >= $coupon->max_uses) {
                        $couponInfo['fail_reasons'][] = 'Coupon usage limit reached';
                    }
                } else {
                    $couponInfo['valid'] = true;

                    [
                        $appliedCode,
                        $couponDiscountAmount,
                        $excludedItems
                    ] = $this->applyCoupon(
                        null,
                        $orderItems,
                        $data['coupon'],
                        $basketDiscount
                    );

                    $couponInfo['excluded_items'] = $excludedItems;

                    if ($appliedCode) {
                        $couponInfo['applied'] = true;
                        $couponInfo['discount'] = round($couponDiscountAmount, 2);
                        $couponInfo['code'] = $appliedCode;

                        // cancel basket discount if coupon applied
                        $basketDiscount = 0;
                    } else {
                        $couponInfo['fail_reasons'][] = 'Coupon conditions not met';
                    }
                }
            } else {
                $couponInfo['fail_reasons'][] = 'No coupon provided';
            }

            // 4️⃣ Totals
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);

            $finalSubtotal = $subtotalAfterProductDiscount
                - ($basketDiscountAmount + $couponDiscountAmount);

            $total = $finalSubtotal + $deliveryPrice;

            return [
                'subtotal_before_discount' => round($subtotalBeforeDiscount, 2),
                'subtotal_after_product_discount' => round($subtotalAfterProductDiscount, 2),

                'basket_discount_percent' => $basketDiscount,
                'basket_discount_amount' => round($basketDiscountAmount, 2),

                'coupon' => $couponInfo,

                'delivery_price' => round($deliveryPrice, 2),

                'total_quantity' => $totalQuantity,
                'subtotal' => round($finalSubtotal, 2),
                'total' => round($total, 2),

                // 'items' => $orderItems,
            ];
        });
    }

    /** -----------------------------
     * Coupon Preview (without creating order)
     * ----------------------------- */
    public function couponPreview(array $data)
    {
        $user = auth('user')->user() ?? User::find(1);

        [$basketDiscount, $deliveryPrice] = $this->resolveBasketAndDelivery(null, $data);

        // Build basket items collection
        $basketItemsCollection = collect();
        foreach ($data['items'] as $item) {
            $shopVariant = ShopProductVariant::with('productVariant.product')->findOrFail($item['shop_product_variant_id']);
            $product = $shopVariant->productVariant->product;
            $price = $shopVariant->price;
            $quantity = $item['quantity'];

            $priceAfterDiscount = ($data['cart_type'] ?? 'default') === CartType::DEFAULT->value
                ? $price * (1 - ($product->discount / 100))
                : $price;

            $basketItemsCollection->push([
                'shop_product_variant_id' => $shopVariant->id,
                'product' => $product,
                'quantity' => $quantity,
                'price' => $price,
                'price_after_discount' => $priceAfterDiscount,
            ]);
        }

        $subtotalAfterProductDiscount = $basketItemsCollection->sum(fn($i) => $i['price_after_discount'] * $i['quantity']);

        $reason = []; // لسبب عدم تطبيق الكوبون
        [$couponCode, $couponDiscountAmount, $excludedItems] = [null, 0, []];

        if (!empty($data['coupon'])) {
            $coupon = Coupon::where('code', $data['coupon'])->first();

            if (!$coupon) {
                $reason[] = 'Coupon not found';
            } elseif (!$coupon->isValid()) {
                if (!$coupon->is_active) $reason[] = 'Coupon is not active';
                if ($coupon->isExpired()) $reason[] = 'Coupon has expired';
                if ($coupon->used_count >= $coupon->max_uses) $reason[] = 'Coupon usage limit reached';
            } else {
                // تحقق من السلة
                $cartType = $data['cart_type'] ?? 'default';
                if ($cartType !== CartType::DEFAULT->value && !config('settings.allow_coupons_on_basket')) {
                    $reason[] = 'Coupons not allowed on this basket type';
                } else {
                    $couponCode = $coupon->code;

                    // تحقق من المنتجات والفئات والبائعين
                    foreach ($basketItemsCollection as $item) {
                        $product = $item['product'];
                        $allowed = true;
                        if ($coupon->products()->exists() && !$coupon->products->contains($product->id)) $allowed = false;
                        if ($coupon->categories()->exists() && !$coupon->categories->contains($product->category_id)) $allowed = false;
                        if ($coupon->vendors()->exists() && !$coupon->vendors->contains($product->vendor_id)) $allowed = false;

                        if (!$allowed) $excludedItems[] = $product->id;
                    }

                    $eligibleSubtotal = $subtotalAfterProductDiscount;
                    if (!empty($excludedItems)) {
                        $eligibleSubtotal = $basketItemsCollection
                            ->whereNotIn('shop_product_variant_id', $excludedItems)
                            ->sum(fn($i) => $i['price_after_discount'] * $i['quantity']);
                        $reason[] = 'Some items are excluded from coupon: ' . implode(',', $excludedItems);
                    }

                    if ($eligibleSubtotal > 0) {
                        $couponDiscountAmount = $coupon->discount_type === 'percent'
                            ? $eligibleSubtotal * ($coupon->discount_value / 100)
                            : min($coupon->discount_value, $eligibleSubtotal);
                    } else {
                        $couponCode = null; // لا يوجد عناصر مؤهلة
                        $reason[] = 'No eligible items for coupon';
                    }
                }
            }
        } else {
            $reason[] = 'No coupon provided';
        }

        $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);
        $finalTotal = $subtotalAfterProductDiscount - ($basketDiscountAmount + $couponDiscountAmount);

        return [
            'subtotal_before_coupon' => round($subtotalAfterProductDiscount, 2),
            'basket_discount' => $basketDiscount,
            'coupon_applied' => !is_null($couponCode),
            'coupon_discount' => round($couponDiscountAmount, 2),
            'subtotal_after_coupon' => round($finalTotal, 2),
            'excluded_items' => $excludedItems,
            'delivery_price' => round($deliveryPrice, 2),
            'total_estimate' => round($finalTotal + $deliveryPrice, 2),
            'coupon_code' => $couponCode,
            'coupon_fail_reasons' => $reason,
        ];
    }


    /** -----------------------------
     * Resolve basket discount & delivery price
     * ----------------------------- */
    protected function resolveBasketAndDelivery($orderOrNull, $data)
    {
        $cartType = $data['cart_type'] ?? CartType::DEFAULT->value;
        $basketDiscount = 0;
        $deliveryPrice = 0;
        $user = auth('user')->user() ?? User::find(1);

        switch ($cartType) {
            case CartType::RECIPE->value:
                $basket = Recipe::findOrFail($data['recipe_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
                break;

            case CartType::ADMIN_CART->value:
                $basket = Basket::findOrFail($data['admin_basket_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
                break;

            case CartType::SCHEDULE_ADMIN_CART->value:
                $basket = Basket::findOrFail($data['admin_schedule_basket_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
                break;

            case CartType::DEFAULT->value:
            default:
                $variantIds = collect($data['items'])->pluck('shop_product_variant_id')->values()->toArray();
                $deliveryPrice = CalculateDeliveryPriceService::handle(
                    user: $user,
                    items: $variantIds,
                    addressId: $data['address_id'] ?? null
                );

                // التحقق من التوصيل المجاني من النقاط
                try {
                    $exchangeService = app(\App\Services\PointExchangeService::class);
                    if ($exchangeService->hasActiveFreeDelivery($user->id)) {
                        $deliveryPrice = 0;
                    }
                } catch (\Throwable $e) {
                    // تجاهل أخطاء النقاط
                }
                break;
        }

        return [$basketDiscount, $deliveryPrice];
    }

    /** -----------------------------
     * Add items to order
     * ----------------------------- */
    protected function addItemsToOrder($order, $data)
    {
        $subtotalBeforeDiscount = 0;
        $subtotalAfterProductDiscount = 0;
        $totalQuantity = 0;
        $orderItems = collect();

        foreach ($data['items'] as $item) {
            $shopVariant = ShopProductVariant::with('productVariant.product')
                ->lockForUpdate()
                ->findOrFail($item['shop_product_variant_id']);

            if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                throw new Exception('Insufficient stock for ' . $shopVariant->productVariant->product->name);
            }

            $product = $shopVariant->productVariant->product;
            $price = $shopVariant->price;
            $quantity = $item['quantity'];
            $productDiscount = ($data['cart_type'] ?? 'default') === CartType::DEFAULT->value ? $product->discount : 0;
            $priceAfterDiscount = $price * (1 - ($productDiscount / 100));

            if ($order) {
                $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'product_name' => $product->name,
                    'variant_attributes' => $shopVariant->productVariant->getAttributesValuesAttribute(),
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $productDiscount,
                ]);
                if (!is_null($shopVariant->quantity)) {
                    $shopVariant->decrement('quantity', $quantity);
                }
            }

            $subtotalBeforeDiscount += $price * $quantity;
            $subtotalAfterProductDiscount += $priceAfterDiscount * $quantity;
            $totalQuantity += $quantity;

            $orderItems->push([
                'shop_product_variant_id' => $shopVariant->id,
                'product' => $product,
                'quantity' => $quantity,
                'price_after_discount' => $priceAfterDiscount,
            ]);
        }

        return [$subtotalBeforeDiscount, $subtotalAfterProductDiscount, $totalQuantity, $orderItems];
    }

    /** -----------------------------
     * Apply Coupon Logic
     * ----------------------------- */
    protected function applyCoupon($orderOrNull, $basketItemsCollection, $couponCode, $basketDiscount)
    {
        $couponDiscountAmount = 0;
        $couponApplied = null;
        $excludedItems = [];

        if (!$couponCode) return [null, 0, []];

        $coupon = Coupon::where('code', $couponCode)->first();
        if (!$coupon || !$coupon->isValid()) return [null, 0, []];

        $cartType = $orderOrNull ? $orderOrNull->cart_type : 'default';
        $canApplyCoupon = $cartType === CartType::DEFAULT->value
            || config('settings.allow_coupons_on_basket');

        if (!$canApplyCoupon) return [null, 0, []];

        if ($cartType !== CartType::DEFAULT->value) {
            $basketDiscount = 0; // cancel basket discount if coupon applied
        }

        // Check excluded items
        foreach ($basketItemsCollection as $item) {
            $product = $item['product'];
            $allowed = true;

            if ($coupon->products()->exists() && !$coupon->products->contains($product->id)) $allowed = false;
            if ($coupon->categories()->exists() && !$coupon->categories->contains($product->category_id)) $allowed = false;
            if ($coupon->vendors()->exists() && !$coupon->vendors->contains($product->vendor_id)) $allowed = false;

            if (!$allowed) $excludedItems[] = $product->id;
        }

        // Eligible subtotal
        $eligibleSubtotal = collect($basketItemsCollection)
            ->whereNotIn('shop_product_variant_id', $excludedItems)
            ->sum(fn($i) => $i['price_after_discount'] * $i['quantity']);

        $couponDiscountAmount = $coupon->discount_type === 'percent'
            ? $eligibleSubtotal * ($coupon->discount_value / 100)
            : min($coupon->discount_value, $eligibleSubtotal);

        $couponApplied = $coupon->code;

        return [
            $coupon->code,
            $couponDiscountAmount,
            $excludedItems,
            $coupon
        ];
    }
}
    /**
     * Update order status and award points if completed
     */
    // public function updateOrderStatus(int $orderId, string $status): bool
    // {
    //     return DB::transaction(function () use ($orderId, $status) {
    //         $order = Order::findOrFail($orderId);
    //         $oldStatus = $order->order_status;
            
    //         $order->update(['order_status' => $status]);

    //         // منح النقاط عند إتمام الطلب
    //         if ($status === OrderStatus::COMPLETED->value && $oldStatus !== OrderStatus::COMPLETED->value) {
    //             try {
    //                 $pointService = app(\App\Services\PointService::class);
                    
    //                 // نقاط أول طلب
    //                 if (!$pointService->isEventCompleted($order->user_id, 'first_order')) {
    //                     $pointService->awardPoints(
    //                         $order->user_id,
    //                         'first_order',
    //                         $order->total,
    //                         'order',
    //                         $order->id
    //                     );
    //                     $pointService->markEventCompleted($order->user_id, 'first_order');
    //                 }
                    
    //                 // نقاط إتمام الطلب (لكل طلب)
    //                 $pointService->awardPoints(
    //                     $order->user_id,
    //                     'order_completion',
    //                     $order->total,
    //                     'order',
    //                     $order->id
    //                 );
                    
    //             } catch (\Throwable $e) {
    //                 // تجاهل أخطاء النقاط لعدم تعطيل تحديث الطلب
    //                 Log::error('Points award failed for order: ' . $orderId, ['error' => $e->getMessage()]);
    //             }
    //         }

    //         return true;
    //     });
    // }

    // /**
    //  * Override update method to handle status changes
    //  */
    // public function update(int $id, array $data)
    // {
    //     if (isset($data['order_status'])) {
    //         $this->updateOrderStatus($id, $data['order_status']);
    //         unset($data['order_status']);
    //     }

    //     if (!empty($data)) {
    //         return parent::update($id, $data);
    //     }

    //     return $this->show($id);
    // }