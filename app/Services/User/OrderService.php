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

            $user = auth('user')->user() ?? User::find(1);

            // Get address
            if ($data['address_id']) {
                $address = $user->addresses()->findOrFail($data['address_id']);
            } else {
                $address = $user->addresses()
                    ->where('is_default', true)
                    ->firstOrFail();
            }

            $order = Order::create([
                'user_id'             => $user->id,
                'user_address_id'     => $address->id,
                'payment_method_id'   => $data['payment_method_id'],
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

            // 4️⃣ Apply point exchanges
            [$pointCouponDiscount, $pointFreeDelivery, $usedCouponExchangeId, $usedFreeDeliveryExchangeId] =
                $this->applyPointExchanges($user->id, $data, $deliveryPrice);

            // ❗ إذا تم تطبيق كوبون أو نقاط أو باقة → نلغي خصم السلة
            $hasExternalDiscount = $couponCode !== null
                || $pointCouponDiscount > 0
                || !empty($data['use_subscription_discount']);

            if ($hasExternalDiscount) {
                $basketDiscount = 0;
            }

            // 5️⃣ Apply Subscription Benefits (only if user explicitly requests)
            $subscriptionBenefitsService = app(\App\Services\User\SubscriptionBenefitsService::class);

            // نستخدم subtotalBeforeDiscount إذا في خصم خارجي، وإلا subtotalAfterProductDiscount
            $baseAmountForSubscription = $hasExternalDiscount
                ? $subtotalBeforeDiscount
                : $subtotalAfterProductDiscount;

            $subscriptionBenefits = $subscriptionBenefitsService->applyBenefits(
                $order,
                $user->id,
                $baseAmountForSubscription,
                $deliveryPrice,
                $data['use_subscription_discount'] ?? false,
                $data['use_subscription_free_delivery'] ?? false
            );

            $subscriptionDiscount = $subscriptionBenefits['discount_amount'] ?? 0;
            $subscriptionFreeDelivery = $subscriptionBenefits['free_delivery_applied'] ?? false;
             $subscriptionPointsBonus =  0;

            // 6️⃣ Calculate totals
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);

            // الـ base amount للحساب النهائي
            $baseAmount = $hasExternalDiscount ? $subtotalBeforeDiscount : $subtotalAfterProductDiscount;

            $finalTotal = $baseAmount
                - ($basketDiscountAmount + $couponDiscountAmount + $pointCouponDiscount + $subscriptionDiscount);

            // Apply free delivery from points or subscription
            if ($pointFreeDelivery || $subscriptionFreeDelivery) {
                $deliveryPrice = 0;
            }

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

                // Point exchange data
                'used_coupon_exchange_id' => $usedCouponExchangeId,
                'used_free_delivery_exchange_id' => $usedFreeDeliveryExchangeId,
                'coupon_discount_from_points' => round($pointCouponDiscount, 2),
                'free_delivery_from_points' => $pointFreeDelivery,

                // Subscription data
                'subscription_id' => $subscriptionBenefits['subscription_id'] ?? null,
                'subscription_discount' => round($subscriptionDiscount, 2),
                'subscription_free_delivery' => $subscriptionFreeDelivery,
                'subscription_points_bonus' => $subscriptionPointsBonus,
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
                ]);
            }


            foreach ($order->items as $item) {

                $variant = ShopProductVariant::find($item->shop_product_variant_id);

                $variant->decrement('quantity', $item->quantity);

                if ($variant->quantity <= 5) {

                    event(new LowStockDetected($variant));
                }
            }

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                null,
                OrderStatus::PENDING->value,
                'system'
            );

            return new $this->resource($order->load('items'));
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

            // تحديد إذا في خصم خارجي
            $hasExternalDiscount = $couponInfo['applied']
                || $pointExchangesInfo['coupon_discount'] > 0
                || !empty($data['use_subscription_discount']);

            // إذا في خصم خارجي → نلغي خصم السلة
            if ($hasExternalDiscount) {
                $basketDiscount = 0;
            }

            // 5️⃣ Preview Subscription Benefits (check if user wants to use them)
            $subscriptionBenefitsService = app(\App\Services\User\SubscriptionBenefitsService::class);

            // نستخدم subtotalBeforeDiscount إذا في خصم خارجي
            $baseAmountForSubscription = $hasExternalDiscount
                ? $subtotalBeforeDiscount
                : $subtotalAfterProductDiscount;

            $subscriptionInfo = $subscriptionBenefitsService->previewBenefits(
                $user->id,
                $baseAmountForSubscription,
                $deliveryPrice
            );

            // Apply subscription discount ONLY if user explicitly requests it
            $subscriptionDiscount = 0;
            $subscriptionFreeDelivery = false;

            if (!empty($data['use_subscription_discount']) && $subscriptionInfo['has_subscription']) {
                $subscriptionDiscount = $subscriptionInfo['discount_amount'] ?? 0;
            }

            if (!empty($data['use_subscription_free_delivery']) && $subscriptionInfo['has_subscription']) {
                $subscriptionFreeDelivery = $subscriptionInfo['free_delivery_applicable'] ?? false;
            }

            // 6️⃣ Totals
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);

            // الـ base amount للحساب النهائي
            $baseAmount = $hasExternalDiscount ? $subtotalBeforeDiscount : $subtotalAfterProductDiscount;

            $finalSubtotal = $baseAmount
                - ($basketDiscountAmount + $couponDiscountAmount + $pointExchangesInfo['coupon_discount'] + $subscriptionDiscount);

            // Apply free delivery from points OR subscription (based on user choice)
            $freeDeliveryApplied = $pointExchangesInfo['free_delivery_applicable'] || $subscriptionFreeDelivery;
            $finalDeliveryPrice = $freeDeliveryApplied ? 0 : $deliveryPrice;
            $total = $finalSubtotal + $finalDeliveryPrice;

            return [
                'subtotal_before_discount' => round($subtotalBeforeDiscount, 2),
                'subtotal_after_product_discount' => round($subtotalAfterProductDiscount, 2),

                'basket_discount_percent' => $basketDiscount,
                'basket_discount_amount' => round($basketDiscountAmount, 2),

                'coupon' => $couponInfo,

                'point_exchanges' => $pointExchangesInfo,

                'subscription' => array_merge($subscriptionInfo, [
                    'discount_applied' => $subscriptionDiscount > 0,
                    'discount_amount_applied' => round($subscriptionDiscount, 2),
                    'free_delivery_applied' => $subscriptionFreeDelivery,
                ]),

                'delivery_price' => round($finalDeliveryPrice, 2),

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

                        if (!$allowed) $excludedItems[] = $item['shop_product_variant_id'];
                    }

                    $eligibleSubtotal = $subtotalAfterProductDiscount;
                    if (!empty($excludedItems)) {
                        $eligibleSubtotal = $basketItemsCollection
                            ->whereNotIn('shop_product_variant_id', (array) $excludedItems)
                            ->sum(fn($i) => $i['price_after_discount'] * $i['quantity']);
                        $reason[] = 'Some items are excluded from coupon: ' . implode(',', array_map('strval', $excludedItems));
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
                // try {
                //     $exchangeService = app(\App\Services\PointExchangeService::class);
                //     if ($exchangeService->hasActiveFreeDelivery($user->id)) {
                //         $deliveryPrice = 0;
                //     }
                // } catch (\Throwable $e) {
                //     // تجاهل أخطاء النقاط
                // }
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
                throw new Exception('Insufficient stock for ' . $shopVariant->productVariant->product->name);
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

    public function reorder($orderId)
    {
        $user = auth('user')->user();

        $originalOrder = Order::with('items')->findOrFail($orderId);

        // Ensure the order belongs to the user
        if ($originalOrder->user_id !== $user->id) {
            throw new CustomExceptionWithMessage('Order not found', 404);
        }

        return DB::transaction(function () use ($originalOrder, $user) {
            // Create new order with same data, but reset some fields
            $newOrder = Order::create([
                'user_id' => $user->id,
                'user_address_id' => $originalOrder->user_address_id,
                'payment_method_id' => $originalOrder->payment_method_id,
                'basket_id' => $originalOrder->basket_id,
                'basket_schedule_id' => $originalOrder->basket_schedule_id,
                'is_instant_delivery' => $originalOrder->is_instant_delivery,
                'status' => OrderStatus::PENDING->value,
                'cart_type' => $originalOrder->cart_type,
                'delivery_price' => $originalOrder->delivery_price,
                'total_quantity' => $originalOrder->total_quantity,
                'total' => $originalOrder->total,
                'subtotal' => $originalOrder->subtotal,
                'basket_discount' => $originalOrder->basket_discount,
                'coupon_discount' => $originalOrder->coupon_discount,
                'affiliate_id' => $originalOrder->affiliate_id,
                'affiliate_rate' => $originalOrder->affiliate_rate,
                'affiliate_source' => $originalOrder->affiliate_source,
                'coupon_id' => $originalOrder->coupon_id,
                'used_coupon_exchange_id' => $originalOrder->used_coupon_exchange_id,
                'used_free_delivery_exchange_id' => $originalOrder->used_free_delivery_exchange_id,
                'coupon_discount_from_points' => $originalOrder->coupon_discount_from_points,
                'free_delivery_from_points' => $originalOrder->free_delivery_from_points,
                'subscription_id' => $originalOrder->subscription_id,
                'subscription_discount' => $originalOrder->subscription_discount,
                'subscription_free_delivery' => $originalOrder->subscription_free_delivery,
                'subscription_points_bonus' => $originalOrder->subscription_points_bonus,
                // Reset timestamps
                'pending_at' => now(),
                'preparing_at' => null,
                'out_delivery_at' => null,
                'delivered_at' => null,
                'driver_id' => null,
                'assigned_by' => null,
            ]);

            // Copy order items
            foreach ($originalOrder->items as $item) {
                $newOrder->items()->create([
                    'shop_product_variant_id' => $item->shop_product_variant_id,
                    'product_name' => $item->product_name,
                    'variant_attributes' => $item->variant_attributes,
                    'item_status' => OrderStatus::PENDING->value,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'pending_at' => now(),
                    'preparing_at' => null,
                    'out_delivery_at' => null,
                    'delivered_at' => null,
                ]);
            }

            // Generate order code
            $newOrder->update(['order_code' => 'ORD-' . $newOrder->id]);

            return new OneResource($newOrder->load('items'));
        });
    }
}
