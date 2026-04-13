<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Events\LowStockDetected;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
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

class OrderServiceNew extends BaseService
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


    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] =  auth('user')->id() ?? 1;
        return parent::getAll($filters, $config);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /** -----------------------------
     * Create Order with Basket + Coupon
     * ----------------------------- */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {

            /** @var User  */
            $user = auth('user')->user();

            /* ============================================================
         | 1️⃣ جلب العنوان
         ============================================================ */
            if (!empty($data['address_id'])) {
                $address = $user->addresses()->findOrFail($data['address_id']);
            } else {
                $address = $user->addresses()
                    ->where('is_default', true)
                    ->firstOrFail();
            }

            /* ============================================================
         | 2️⃣ إنشاء الطلب
         ============================================================ */
            $order = Order::create([
                'user_id'             => $user->id,
                'user_address_id'     => $address->id,
                'payment_method_id'   => $data['payment_method_id'],
                'cart_type'           => $data['cart_type'] ?? CartType::DEFAULT->value,
                'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
                'status'              => OrderStatus::PENDING->value,
            ]);

            /* ============================================================
         | 3️⃣ حساب خصم السلة + سعر التوصيل
         ============================================================ */
            [$basketDiscountPercent, $deliveryPrice] =
                $this->resolveBasketAndDelivery($order, $data);

            /* ============================================================
         | 4️⃣ إضافة العناصر (يحسِب خصم المنتج داخلياً)
         ============================================================ */
            [
                $subtotalBeforeDiscount,
                $subtotalAfterProductDiscount,
                $totalQuantity,
                $orderItems
            ] = $this->addItemsToOrder($order, $data);

            /* ============================================================
         | 5️⃣ تطبيق العروض غير المالية (gift / buy_x_get_y)
         ============================================================ */
            if (!empty($data['promotion_id'])) {

                $promotionService = app(PromotionService::class);

                $promotionService->applyNonDiscountPromotions(
                    $order,
                    collect($orderItems),
                    $data['promotion_id'],
                    $subtotalBeforeDiscount,
                    $user->id,
                    false
                );
            }

            /* ============================================================
         | 6️⃣ حساب الخصومات الخارجية (واحد فقط مفروض)
         ============================================================ */
            $externalDiscountAmount = 0;

            // 🔹 كوبون
            if (!empty($data['coupon'])) {
                [$appliedCode, $couponDiscount] =
                    $this->applyCoupon($order, $orderItems, $data['coupon'], 0);

                $externalDiscountAmount += $couponDiscount ?? 0;
            }

            // 🔹 نقاط
            if (!empty($data['point_coupon_exchange_id'])) {
                [$pointDiscount] =
                    $this->applyPointExchanges($user->id, $data, $deliveryPrice);

                $externalDiscountAmount += $pointDiscount ?? 0;
            }

            // 🔹 اشتراك
            if (!empty($data['use_subscription_discount'])) {

                $subscriptionService = app(SubscriptionBenefitsService::class);

                $subscriptionResult = $subscriptionService->applyBenefits(
                    $order,
                    $user->id,
                    $subtotalBeforeDiscount,
                    $deliveryPrice,
                    true,
                    false
                );

                $externalDiscountAmount +=
                    $subscriptionResult['discount_amount'] ?? 0;
            }

            // 🔹 عرض خصم (spend_x_discount / simple_discount)
            if (!empty($data['promotion_id'])) {

                $promotionService = app(PromotionService::class);

                $promotionDiscount =
                    $promotionService->applyDiscountPromotion(
                        $order,
                        $data['promotion_id'],
                        $subtotalBeforeDiscount
                    );

                $externalDiscountAmount += $promotionDiscount ?? 0;
            }

            /* ============================================================
         | 7️⃣ تحديد أولوية الخصومات
         ============================================================ */

            $hasExternalDiscount = $externalDiscountAmount > 0;

            // إذا في خصم خارجي → نلغي خصم السلة
            if ($hasExternalDiscount) {
                $basketDiscountPercent = 0;
            }

            // تحديد المبلغ الأساسي
            if ($hasExternalDiscount) {
                // خصم خارجي يلغي خصم المنتج والسلة
                $baseAmount = $subtotalBeforeDiscount;
            } elseif ($basketDiscountPercent > 0) {
                // خصم سلة يلغي خصم المنتج
                $baseAmount = $subtotalBeforeDiscount;
            } else {
                // فقط خصم منتج
                $baseAmount = $subtotalAfterProductDiscount;
            }

            $basketDiscountAmount =
                $baseAmount * ($basketDiscountPercent / 100);

            /* ============================================================
         | 8️⃣ التوصيل المجاني (نقاط / اشتراك)
         ============================================================ */

            $finalDeliveryPrice = $deliveryPrice;

            if (!empty($data['point_free_delivery_exchange_id'])) {
                $finalDeliveryPrice = 0;
            }

            if (!empty($data['use_subscription_free_delivery'])) {
                $finalDeliveryPrice = 0;
            }

            /* ============================================================
         | 9️⃣ الحساب النهائي
         ============================================================ */

            $finalTotal =
                $baseAmount
                - $basketDiscountAmount
                - $externalDiscountAmount
                + $finalDeliveryPrice;

            $order->update([
                'subtotal'        => $subtotalBeforeDiscount,
                'discount_amount' => round(
                    $basketDiscountAmount + $externalDiscountAmount,
                    2
                ),
                'delivery_price'  => $finalDeliveryPrice,
                'total'           => round($finalTotal, 2),
            ]);

            /* ============================================================
         | 🔟 إطلاق حدث إنشاء الطلب
         ============================================================ */
            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                null,
                OrderStatus::PENDING->value,
                'user'
            );

            return new OneResource($order->fresh('items'));
        });
    }

    public function preview(array $data)
    {
        return DB::transaction(function () use ($data) {

            $user = auth('user')->user() ?? User::find(1);

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

            // 4️⃣ Check Point Exchanges (without modifying them)
            $pointExchangesInfo = $this->checkPointExchanges($user->id, $data, $deliveryPrice);

            // 5️⃣ Totals
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);

            $finalSubtotal = $subtotalAfterProductDiscount
                - ($basketDiscountAmount + $couponDiscountAmount + $pointExchangesInfo['coupon_discount']);

            $finalDeliveryPrice = $pointExchangesInfo['free_delivery_applicable'] ? 0 : $deliveryPrice;
            $total = $finalSubtotal + $finalDeliveryPrice;

            return [
                'subtotal_before_discount' => round($subtotalBeforeDiscount, 2),
                'subtotal_after_product_discount' => round($subtotalAfterProductDiscount, 2),

                'basket_discount_percent' => $basketDiscount,
                'basket_discount_amount' => round($basketDiscountAmount, 2),

                'coupon' => $couponInfo,

                'point_exchanges' => $pointExchangesInfo,

                'delivery_price' => round($finalDeliveryPrice, 2),

                'total_quantity' => $totalQuantity,
                'subtotal' => round($finalSubtotal, 2),
                'total' => round($total, 2),

                // 'items' => $orderItems,
            ];
        });
    }

    /** -----------------------------
     * Resolve basket discount & delivery price
     * ----------------------------- */
    protected function resolveBasketAndDelivery($orderOrNull, $data)
    {
        //admin basket id
        //scedule id
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
                // Support both admin_schedule_basket_id and admin_basket_id for backward compatibility
                $basketId = $data['admin_schedule_basket_id'] ?? $data['admin_basket_id'] ?? null;

                if (!$basketId) {
                    throw new CustomExceptionWithMessage('custom.orders.basket_id_required');
                }

                $basket = Basket::findOrFail($basketId);

                // Check if user selected a specific schedule
                if (!empty($data['basket_schedule_id'])) {
                    $basketSchedule = $basket->schedules()
                        ->where('id', $data['basket_schedule_id'])
                        ->where('is_active', true)
                        ->first();

                    if ($basketSchedule && $basketSchedule->discount_value > 0) {
                        // Use selected schedule discount
                        $basketDiscount = $basketSchedule->discount_value;
                    } else {
                        // Fallback to basket discount if schedule not found or no discount
                        $basketDiscount = $basket->discount;
                    }
                } else {
                    // No schedule selected, use basket discount only
                    $basketDiscount = $basket->discount;
                }

                $deliveryPrice = $basket->delivery_price;
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

        // تحديد إذا في خصم خارجي (كوبون، نقاط، باقة)
        $hasExternalDiscount = !empty($data['coupon'])
            || !empty($data['point_coupon_exchange_id'])
            || !empty($data['use_subscription_discount']);

        // تحديد إذا في سلة (basket/recipe/schedule)
        $cartType = $data['cart_type'] ?? 'default';
        $hasBasket = $cartType !== CartType::DEFAULT->value;

        foreach ($data['items'] as $item) {
            $shopVariant = ShopProductVariant::with('productVariant.product')
                ->lockForUpdate()
                ->findOrFail($item['shop_product_variant_id']);

            if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.insufficient_stock',
                    400,
                    ['product' => $shopVariant->productVariant->product->name]
                );
            }

            $product = $shopVariant->productVariant->product;
            $price = $shopVariant->price;
            $quantity = $item['quantity'];

            // تطبيق خصم المنتج فقط إذا:
            // 1. ما في خصم خارجي (كوبون/نقاط/باقة)
            // 2. ما في سلة (basket/recipe/schedule)
            // 3. cart_type = default
            $productDiscount = 0;
            if (!$hasExternalDiscount && !$hasBasket && $cartType === CartType::DEFAULT->value) {
                $productDiscount = $product->discount;
            }

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

        if (!$couponCode) return [null, 0, [], null];

        $coupon = Coupon::where('code', $couponCode)->first();
        if (!$coupon || !$coupon->isValid()) return [null, 0, [], null];

        $cartType = $orderOrNull ? $orderOrNull->cart_type : 'default';
        $canApplyCoupon = $cartType === CartType::DEFAULT->value
            || config('settings.allow_coupons_on_basket');

        if (!$canApplyCoupon) return [null, 0, [], null];

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

    /** -----------------------------
     * Apply Point Exchanges
     * ----------------------------- */
    protected function applyPointExchanges(int $userId, array $data, float $deliveryPrice): array
    {
        $pointCouponDiscount = 0;
        $pointFreeDelivery = false;
        $usedCouponExchangeId = null;
        $usedFreeDeliveryExchangeId = null;

        Log::info('🔍 Starting applyPointExchanges', [
            'user_id' => $userId,
            'point_coupon_exchange_id' => $data['point_coupon_exchange_id'] ?? null,
            'point_free_delivery_exchange_id' => $data['point_free_delivery_exchange_id'] ?? null,
            'delivery_price' => $deliveryPrice
        ]);

        try {
            $exchangeService = app(\App\Services\PointExchangeService::class);

            // Apply coupon from points
            if (!empty($data['point_coupon_exchange_id'])) {
                Log::info('🎟️ Processing coupon exchange', ['exchange_id' => $data['point_coupon_exchange_id']]);

                $exchange = \App\Models\PointExchange::where('id', $data['point_coupon_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'coupon')
                    ->where('status', 'completed')
                    ->first();

                Log::info('🎟️ Coupon exchange query result', [
                    'found' => $exchange ? 'yes' : 'no',
                    'exchange_data' => $exchange ? $exchange->toArray() : null
                ]);

                if ($exchange && $exchangeService->isExchangeValid($exchange)) {
                    $pointCouponDiscount = $exchange->exchange_data['discount_amount'] ?? 0;
                    $usedCouponExchangeId = $exchange->id;

                    Log::info('✅ Coupon exchange applied', [
                        'discount_amount' => $pointCouponDiscount,
                        'exchange_id' => $usedCouponExchangeId
                    ]);

                    // Mark as used
                    $exchangeService->markExchangeAsUsed($exchange->id);
                } else {
                    Log::warning('❌ Coupon exchange validation failed', [
                        'exchange_exists' => $exchange ? 'yes' : 'no',
                        'is_valid' => $exchange ? $exchangeService->isExchangeValid($exchange) : 'N/A'
                    ]);
                }
            }

            // Apply free delivery from points
            if (!empty($data['point_free_delivery_exchange_id'])) {
                Log::info('🚚 Processing free delivery exchange', ['exchange_id' => $data['point_free_delivery_exchange_id']]);

                $exchange = \App\Models\PointExchange::where('id', $data['point_free_delivery_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'free_delivery')
                    ->where('status', 'completed')
                    ->first();

                Log::info('🚚 Free delivery exchange query result', [
                    'found' => $exchange ? 'yes' : 'no',
                    'exchange_data' => $exchange ? $exchange->toArray() : null
                ]);

                if ($exchange && $exchangeService->isExchangeValid($exchange)) {
                    $pointFreeDelivery = true;
                    $usedFreeDeliveryExchangeId = $exchange->id;

                    Log::info('✅ Free delivery exchange applied', [
                        'exchange_id' => $usedFreeDeliveryExchangeId
                    ]);

                    // Mark as used
                    $exchangeService->markExchangeAsUsed($exchange->id);
                } else {
                    Log::warning('❌ Free delivery exchange validation failed', [
                        'exchange_exists' => $exchange ? 'yes' : 'no',
                        'is_valid' => $exchange ? $exchangeService->isExchangeValid($exchange) : 'N/A',
                        'exchange_status' => $exchange->status ?? 'N/A',
                        'expires_at' => $exchange->exchange_data['expires_at'] ?? 'N/A'
                    ]);
                }
            }

            Log::info('🏁 applyPointExchanges completed', [
                'coupon_discount' => $pointCouponDiscount,
                'free_delivery' => $pointFreeDelivery,
                'used_coupon_id' => $usedCouponExchangeId,
                'used_free_delivery_id' => $usedFreeDeliveryExchangeId
            ]);
        } catch (\Throwable $e) {
            // تجاهل أخطاء النقاط لعدم تعطيل إنشاء الطلب
            Log::error('❌ Point exchange application failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return [$pointCouponDiscount, $pointFreeDelivery, $usedCouponExchangeId, $usedFreeDeliveryExchangeId];
    }

    /** -----------------------------
     * Check Point Exchanges (Preview Only - No Modifications)
     * ----------------------------- */
    protected function checkPointExchanges(int $userId, array $data, float $deliveryPrice): array
    {
        $result = [
            'coupon' => [
                'provided' => !empty($data['point_coupon_exchange_id']),
                'valid' => false,
                'applicable' => false,
                'exchange_id' => $data['point_coupon_exchange_id'] ?? null,
                'discount_amount' => 0,
                'fail_reasons' => [],
            ],
            'free_delivery' => [
                'provided' => !empty($data['point_free_delivery_exchange_id']),
                'valid' => false,
                'applicable' => false,
                'exchange_id' => $data['point_free_delivery_exchange_id'] ?? null,
                'fail_reasons' => [],
            ],
            'coupon_discount' => 0,
            'free_delivery_applicable' => false,
        ];

        try {
            $exchangeService = app(\App\Services\PointExchangeService::class);

            // Check coupon exchange
            if (!empty($data['point_coupon_exchange_id'])) {
                $exchange = \App\Models\PointExchange::where('id', $data['point_coupon_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'coupon')
                    ->where('status', 'completed')
                    ->first();

                if (!$exchange) {
                    $result['coupon']['fail_reasons'][] = 'Exchange not found or not owned by user';
                } elseif (!$exchangeService->isExchangeValid($exchange)) {
                    $result['coupon']['fail_reasons'][] = 'Exchange has expired';
                    $result['coupon']['valid'] = false;
                } else {
                    $result['coupon']['valid'] = true;
                    $result['coupon']['applicable'] = true;
                    $result['coupon']['discount_amount'] = $exchange->exchange_data['discount_amount'] ?? 0;
                    $result['coupon_discount'] = $exchange->exchange_data['discount_amount'] ?? 0;
                }
            } else {
                $result['coupon']['fail_reasons'][] = 'No coupon exchange provided';
            }

            // Check free delivery exchange
            if (!empty($data['point_free_delivery_exchange_id'])) {
                $exchange = \App\Models\PointExchange::where('id', $data['point_free_delivery_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'free_delivery')
                    ->where('status', 'completed')
                    ->first();

                if (!$exchange) {
                    $result['free_delivery']['fail_reasons'][] = 'Exchange not found or not owned by user';
                } elseif (!$exchangeService->isExchangeValid($exchange)) {
                    $result['free_delivery']['fail_reasons'][] = 'Exchange has expired';
                    $result['free_delivery']['valid'] = false;
                } else {
                    $result['free_delivery']['valid'] = true;
                    $result['free_delivery']['applicable'] = true;
                    $result['free_delivery_applicable'] = true;
                }
            } else {
                $result['free_delivery']['fail_reasons'][] = 'No free delivery exchange provided';
            }
        } catch (\Throwable $e) {
            Log::error('Point exchange check failed in preview', ['error' => $e->getMessage()]);
            $result['coupon']['fail_reasons'][] = 'System error checking exchange';
            $result['free_delivery']['fail_reasons'][] = 'System error checking exchange';
        }

        return $result;
    }

    public function cancel(int $orderId)
    {
        $userId = auth('user')->id();

        $order = Order::with('items')->where('id', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($order->status !== OrderStatus::PENDING->value) {
            throw new CustomExceptionWithMessage('custom.orders.cannot_cancel');
        }

        foreach ($order->items as $item) {
            if ($item->item_status !== OrderStatus::PENDING->value) {
                throw new CustomExceptionWithMessage('custom.orders.items_cannot_cancel');
            }
        }
        $oldStatus = $order->status;

        $order->update([
            'status' => OrderStatus::CANCELLED->value
        ]);

        foreach ($order->items as $item) {
            $item->update([
                'status' => OrderStatus::CANCELLED->value
            ]);
        }

        event(new OrderStatusChanged(
            order: $order,
            from: $oldStatus,
            to: OrderStatus::CANCELLED->value,
            changedBy: 'user'
        ));
    }

    public function activeOrder()
    {
        $userId = auth('user')->id();
        $order = Order::with('items')
            ->where('user_id', $userId)
            ->whereNotIn('status', [
                OrderStatus::DELIVERED->value,
                OrderStatus::CANCELLED->value,
            ])
            ->latest()
            ->first();
        return $order ? OneResource::make($order) : null;
    }
}
