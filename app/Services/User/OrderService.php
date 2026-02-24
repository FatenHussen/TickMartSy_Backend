<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
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

            // 4️⃣ Apply point exchanges
            [$pointCouponDiscount, $pointFreeDelivery, $usedCouponExchangeId, $usedFreeDeliveryExchangeId] =
                $this->applyPointExchanges($user->id, $data, $deliveryPrice);

            // ❗ إذا تم تطبيق كوبون → نلغي خصم السلة
            if ($couponCode !== null) {
                $basketDiscount = 0;
            }

            // 5️⃣ Calculate totals
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);

            $finalTotal = $subtotalAfterProductDiscount
                - ($basketDiscountAmount + $couponDiscountAmount + $pointCouponDiscount);

            // Apply free delivery from points
            if ($pointFreeDelivery) {
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

            // OrderCreated::dispatch($order);

            OrderStatusChanged::dispatch(
                $order->fresh('items'),
                null, // null يعني طلب جديد
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
                $basket = Basket::findOrFail($data['admin_schedule_basket_id']);
                //schedule
                $basketDiscount = $basket->discount; //change
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
