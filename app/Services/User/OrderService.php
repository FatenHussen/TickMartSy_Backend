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

            /** @var User */
            $user = auth('user')->user() ?? User::find(1);

            // =====================
            // 1️⃣ Address
            // =====================
            $address = $user->addresses()->findOrFail($data['address_id']);

            // =====================
            // 2️⃣ Create Order
            // =====================
            $order = Order::create([
                'user_id' => $user->id,
                'user_address_id' => $address->id,
                'cart_type' => $data['cart_type'] ?? CartType::DEFAULT->value,
                'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
                'status' => OrderStatus::PENDING->value,

                // النقاط
                'used_coupon_exchange_id' => $data['point_coupon_exchange_id'] ?? null,
                'used_free_delivery_exchange_id' => $data['point_free_delivery_exchange_id'] ?? null,

                // الاشتراك
                'subscription_free_delivery' => $data['use_subscription_free_delivery'] ?? false,
                'subscription_discount' => 0,
                'subscription_points_bonus' => 0,

                'promotion_id' => $data['promotion_id'] ?? null,
                'promotion_discount' => 0,
            ]);

            // =====================
            // 3️⃣ Basket + Delivery
            // =====================
            [$basketDiscountPercent, $deliveryPrice] = $this->resolveBasketAndDelivery($order, $data);

            // =====================
            // 4️⃣ Items
            // =====================
            [
                $subtotalBeforeDiscount,
                $subtotalAfterProductDiscount,
                $totalQuantity,
                $orderItems
            ] = $this->addItemsToOrder($order, $data);

            // =====================
            // 5️⃣ Non Discount Promotions (buy_x_get_y)
            // =====================
            $promotionService = app(PromotionService::class);
            $nonDiscountPromotions = $promotionService->applyNonDiscountPromotions($order, $orderItems);

            // =====================
            // 6️⃣ External Discounts
            // =====================
            $externalDiscountAmount = 0;
            $couponDiscount = 0;
            $couponDiscountFromPoints = 0;
            $subscriptionDiscount = 0;
            $promotionDiscount = 0;
            $freeDeliveryFromPoints = 0;

            /** Coupon */
            if (!empty($data['coupon'])) {

                [$appliedCode, $couponDiscount, $excludedItems, $coupon] = $this->applyCoupon($order, $orderItems, $data['coupon'], 0);
                if ($appliedCode) {
                    $order->update([
                        'coupon_id' => $coupon->id,
                        'coupon_discount' => round($couponDiscount, 2)
                    ]);
                    $externalDiscountAmount += $couponDiscount;
                }
            }

            /** Points */
            [$pointsDiscount, $pointsFreeDelivery] = $this->applyPointExchanges($user->id, $data, $deliveryPrice);
            $couponDiscountFromPoints = $pointsDiscount;
            $freeDeliveryFromPoints = $pointsFreeDelivery;
            $externalDiscountAmount += $pointsDiscount;
            if ($pointsFreeDelivery) $deliveryPrice = 0;

            /** Subscription */
            if (!empty($data['use_subscription_discount']) || !empty($data['use_subscription_free_delivery'])) {
                $subscriptionResult = app(SubscriptionBenefitsService::class)->applyBenefits(
                    $order,
                    $user->id,
                    $subtotalBeforeDiscount,
                    $deliveryPrice,
                    !empty($data['use_subscription_discount']),
                    !empty($data['use_subscription_free_delivery'])
                );

                $subscriptionDiscount = $subscriptionResult['discount_amount'] ?? 0;
                $externalDiscountAmount += $subscriptionDiscount;

                if (!empty($data['use_subscription_free_delivery']) && ($subscriptionResult['free_delivery_applied'] ?? false)) {
                    $deliveryPrice = 0;
                }
            }

            /** Promotion (financial) */
            if (!empty($data['promotion_id'])) {
                $promotionDiscount = $promotionService->applyDiscountPromotion(
                    $order,
                    $data['promotion_id'],
                    $subtotalBeforeDiscount
                );
                $externalDiscountAmount += $promotionDiscount;
            }

            // =====================
            // 7️⃣ Basket Discount
            // =====================
            $hasExternalDiscount = $externalDiscountAmount > 0;
            $basketDiscountAmount = !$hasExternalDiscount && $basketDiscountPercent > 0
                ? $subtotalBeforeDiscount * $basketDiscountPercent / 100
                : 0;

            // =====================
            // 8️⃣ Final Total
            // =====================
            $finalTotal = $subtotalBeforeDiscount
                - $basketDiscountAmount
                - $externalDiscountAmount
                + $deliveryPrice;

            // =====================
            // 9️⃣ Update Order
            // =====================
            $order->update([
                'subtotal' => $subtotalBeforeDiscount,
                'basket_discount' => round($basketDiscountAmount, 2),
                'coupon_discount' => round($couponDiscount, 2),
                'coupon_discount_from_points' => round($couponDiscountFromPoints, 2),
                'free_delivery_from_points' => $freeDeliveryFromPoints,
                'subscription_discount' => round($subscriptionDiscount, 2),
                'promotion_discount' => round($promotionDiscount, 2),
                'delivery_price' => $deliveryPrice,
                'total_quantity' => $totalQuantity,
                'total' => round($finalTotal, 2),
            ]);

            // =====================
            // 🔟 Event
            // =====================
            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                null,
                OrderStatus::PENDING->value,
                'user'
            );

            return new OneResource($order->fresh('items'));
        });
    }

    public function preview(array $data): array
    {
        $user = auth('user')->user() ?? User::find(1);

        [$basketDiscountPercent, $deliveryPrice] = $this->resolveBasketAndDelivery(null, $data);

        [
            $subtotalBeforeDiscount,
            $subtotalAfterProductDiscount,
            $totalQuantity,
            $orderItems
        ] = $this->addItemsToOrder(null, $data);

        $externalDiscountAmount = 0;
        $couponDiscount = 0;
        $subscriptionDiscount = 0;
        $subscriptionResult = [];
        $freeDeliveryApplied = false;

        // =====================
        // 1️⃣ Coupon
        // =====================
        if (!empty($data['coupon'])) {
            $coupon = Coupon::where('code', $data['coupon'])->first();
            if ($coupon) {
                $couponDiscount = $coupon->discount_value;
                $externalDiscountAmount += $couponDiscount;
            }
        }

        // =====================
        // 2️⃣ Points (Preview)
        // =====================
        $pointsResult = $this->checkPointExchanges($user->id, $data, $deliveryPrice);

        $pointDiscount = $pointsResult['coupon_discount'] ?? 0;
        $pointsFreeDelivery = $pointsResult['free_delivery_applicable'] ?? false;

        if ($pointDiscount > 0) {
            $externalDiscountAmount += $pointDiscount;
        }

        if ($pointsFreeDelivery) {
            $deliveryPrice = 0;
        }

        // =====================
        // 2️⃣ Subscription
        // =====================
        if (!empty($data['use_subscription_discount']) || !empty($data['use_subscription_free_delivery'])) {
            $subscriptionService = app(SubscriptionBenefitsService::class);
            $subscriptionResult = $subscriptionService->previewBenefits(
                $user->id,
                $subtotalBeforeDiscount,
                $deliveryPrice
            );

            if (!empty($data['use_subscription_discount'])) {
                $subscriptionDiscount = $subscriptionResult['discount_amount'] ?? 0;
                $externalDiscountAmount += $subscriptionDiscount;
            }

            if (!empty($data['use_subscription_free_delivery']) && ($subscriptionResult['free_delivery_applicable'] ?? false)) {
                $deliveryPrice = 0;
                $freeDeliveryApplied = true;
            }
        }



        // =====================
        // 3️⃣ Promotions
        // =====================
        $promotionService = app(\App\Services\User\PromotionService::class);


        // العروض القابلة للاختيار
        $availablePromotions = $promotionService->getAvailablePromotions($subtotalBeforeDiscount, $orderItems);

        $promotionDiscount = 0;
        $nonDiscountPromotions = [];
        $freeItems = [];

        if (!empty($data['promotion_id'])) {
            // خصم مالي إذا كان financial promotion
            $promotionDiscount = $promotionService->applyDiscountPromotion(
                null,
                $data['promotion_id'],
                $subtotalBeforeDiscount
            );


            $externalDiscountAmount += $promotionDiscount;
        }

        // الهدايا إذا buy_x_get_y
        $appliedNonDiscount = $promotionService->applyNonDiscountPromotions(
            null,
            $orderItems,
        );

        $nonDiscountPromotions = $appliedNonDiscount;


        // =====================
        // 4️⃣ Basket Discount
        // =====================
        $hasExternalDiscount = $externalDiscountAmount > 0;
        $basketDiscountAmount = !$hasExternalDiscount && $basketDiscountPercent > 0
            ? $subtotalBeforeDiscount * $basketDiscountPercent / 100
            : 0;

        // =====================
        // 5️⃣ Final Totals
        // =====================
        $finalTotal = $subtotalBeforeDiscount - $basketDiscountAmount - $externalDiscountAmount + $deliveryPrice;

        // تصحيح products_total_after_all_discounts
        $productsTotalAfterAllDiscounts = $hasExternalDiscount
            ? $subtotalBeforeDiscount - $externalDiscountAmount
            : $subtotalAfterProductDiscount - $basketDiscountAmount;

        return [
            'discounts' => [
                'basket' => [
                    'percent' => $basketDiscountPercent,
                    'amount'  => round($basketDiscountAmount, 2),
                ],
                'external' => [
                    'amount' => round($externalDiscountAmount, 2),
                    'source' => $couponDiscount > 0 ? 'coupon'
                        : (!empty($subscriptionDiscount) ? 'subscription' : (!empty($promotionDiscount) ? 'promotion' : null)),
                ],
                'coupon' => [
                    'provided' => !empty($data['coupon']),
                    'applied'  => $couponDiscount > 0,
                    'code'     => $data['coupon'] ?? null,
                    'discount' => $couponDiscount,
                ],
                'points' => [
                    'coupon_discount'       => $pointDiscount ?? 0,
                    'free_delivery_applied' => $freeDeliveryApplied ?? false,
                ],
                'subscription' => $subscriptionResult ?? ['has_subscription' => false],
                'promotion' => [
                    'id' => $data['promotion_id'] ?? null,
                    'discount_amount' => $promotionDiscount,
                ],
            ],
            'delivery' => [
                'price' => $deliveryPrice,
            ],
            'subtotal_before_discount' => $subtotalBeforeDiscount,
            'subtotal_after_product_discount' => $subtotalAfterProductDiscount,
            'products_total_after_all_discounts' => round($productsTotalAfterAllDiscounts, 2),
            'total_quantity' => $totalQuantity,
            'total' => round($finalTotal, 2),
            'non_discount_promotions' => $nonDiscountPromotions,
            'available_promotions' => $availablePromotions,
            // 'all_promotions' => $allPromotions, // ممكن تستخدمها لعرض كل العروض في البريفيو
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
                    throw new Exception('Basket ID is required for scheduled admin cart');
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
     * Add items to order / preview
     * ----------------------------- */
    protected function addItemsToOrder($order, $data)
    {
        $subtotalBeforeDiscount = 0;
        $subtotalAfterProductDiscount = 0;
        $totalQuantity = 0;
        $orderItems = collect();

        $isPreview = !$order;

        Log::info('ADD ITEMS START', [
            'mode' => $isPreview ? 'preview' : 'create'
        ]);

        // تحديد إذا في خصم خارجي
        $hasExternalDiscount = !empty($data['coupon'])
            || !empty($data['point_coupon_exchange_id'])
            || !empty($data['use_subscription_discount']);

        // تحديد نوع السلة
        $cartType = $data['cart_type'] ?? 'default';
        $hasBasket = $cartType !== CartType::DEFAULT->value;

        foreach ($data['items'] as $item) {

            $shopVariant = ShopProductVariant::with('productVariant.product')
                ->when(!$isPreview, fn($q) => $q->lockForUpdate())
                ->findOrFail($item['shop_product_variant_id']);

            if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                throw new Exception(
                    'Insufficient stock for ' . $shopVariant->productVariant->product->name
                );
            }

            $product = $shopVariant->productVariant->product;
            $price = $shopVariant->price;
            $quantity = $item['quantity'];

            // تطبيق خصم المنتج فقط في حالة عدم وجود خصم خارجي
            $productDiscount = 0;

            if (!$hasExternalDiscount && !$hasBasket && $cartType === CartType::DEFAULT->value) {
                $productDiscount = $product->discount;
            }

            $priceAfterDiscount = $price * (1 - ($productDiscount / 100));

            Log::info('ITEM CALCULATION', [
                'product' => $product->name,
                'price' => $price,
                'quantity' => $quantity,
                'product_discount' => $productDiscount,
                'price_after_discount' => $priceAfterDiscount
            ]);

            /**
             * حفظ في قاعدة البيانات فقط إذا كان order موجود
             */
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
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price' => $price,
                'product_discount' => $productDiscount,
                'price_after_discount' => $priceAfterDiscount,
                'product' => $product,

            ]);
        }

        Log::info('ITEMS RESULT', [
            'subtotal_before_discount' => $subtotalBeforeDiscount,
            'subtotal_after_product_discount' => $subtotalAfterProductDiscount,
            'total_quantity' => $totalQuantity
        ]);

        return [
            $subtotalBeforeDiscount,
            $subtotalAfterProductDiscount,
            $totalQuantity,
            $orderItems
        ];
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

        // $cartType = $orderOrNull ? $orderOrNull->cart_type : 'default';
        // $canApplyCoupon = $cartType === CartType::DEFAULT->value
        //     || config('settings.allow_coupons_on_basket');

        // if (!$canApplyCoupon) return [null, 0, [], null];

        // if ($cartType !== CartType::DEFAULT->value) {
        //     $basketDiscount = 0; // cancel basket discount if coupon applied
        // }

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


    public function cancel(int $orderId)
    {
        $userId = auth('user')->id();

        $order = Order::with('items')->where('id', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($order->status !== OrderStatus::PENDING->value) {
            throw new CustomExceptionWithMessage('Order cannot be cancelled');
        }

        foreach ($order->items as $item) {
            if ($item->item_status !== OrderStatus::PENDING->value) {
                throw new CustomExceptionWithMessage('Some items cannot be cancelled');
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
