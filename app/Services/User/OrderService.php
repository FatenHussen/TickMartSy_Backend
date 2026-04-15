<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Events\LowStockDetected;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Exceptions\CustomExceptionWithMessage;
use App\Http\Resources\Order\OneResource as OneResource;
use App\Http\Resources\Order\AllResource;
use App\Models\AffiliateWalletTransaction;
use App\Models\Basket;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Recipe;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Models\Coupon;
use App\Models\PointExchange;
use App\Services\BaseService;
use App\Services\PointExchangeService;
use App\Services\User\CalculateDeliveryPriceService;
use App\Services\User\PromotionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\InventoryService;
use App\Traits\HasCurrencyConversion;

class OrderService extends BaseService
{
    use HasCurrencyConversion;
    public function __construct(Order $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;

        $this->searchableFields = ['total', 'total_quantity'];
        $this->sortableFields   = ['id', 'total', 'total_quantity'];
        $this->relations = [
            'user',
            'address',
            'paymentMethod',
            'coupon',
            'items',
            'items.extras',
            'items.extras.extraDetail',
            'items.shopProductVariant.shop',
            'items.shopProductVariant.productVariant.product',
        ];
        $this->pagination       = true;
    }


    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] =  auth('user')->id() ?? 1;
        return parent::getAll($filters, $config);
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $status = $filters['status'] ?? null;
        $isRestaurant = array_key_exists('is_restaurant', $filters) ? $filters['is_restaurant'] : null;
        $shopType = $filters['shop_type'] ?? null;

        unset($filters['status'], $filters['is_restaurant'], $filters['shop_type']);

        $query = parent::queryBuilder($query, $filters, $config);

        // Status filter
        if (!empty($status)) {
            $query->where('status', $status);
        }

        if ($isRestaurant !== null) {
            $query->whereHas('items.shopProductVariant.shop', function ($shopQuery) use ($isRestaurant) {
                $shopQuery->where('is_restaurant', filter_var($isRestaurant, FILTER_VALIDATE_BOOLEAN));
            });
        }

        if (!empty($shopType)) {
            $query->whereHas('items.shopProductVariant.shop', function ($shopQuery) use ($shopType) {
                if ($shopType === 'restaurant') {
                    $shopQuery->where('is_restaurant', true);
                    return;
                }

                if ($shopType === 'store') {
                    $shopQuery
                        ->where('is_restaurant', false)
                        ->where('is_service_provider', false);
                }
            });
        }

        return $query;
    }

    /**
     * CREATE ORDER
     */
    public function create($data)
    {
        return DB::transaction(function () use ($data) {

            $user = $this->resolveUser();
            $address = $this->resolveAddress($user, $data['address_id']);

            $order = $this->createOrder($user, $address, $data);

            [$basketDiscountPercent, $deliveryPrice] =
                $this->resolveBasketAndDelivery($order, $data);

            [
                $subtotalBeforeDiscount,
                $subtotalAfterProductDiscount,
                $totalQuantity,
                $orderItems
            ] = $this->addItemsToOrder($order, $data);

            $promotionService = app(PromotionService::class);

            $discounts = $this->applyExternalDiscounts(
                $order,
                $user,
                $data,
                $orderItems,
                $subtotalBeforeDiscount,
                $deliveryPrice
            );

            $externalDiscount = $discounts['total_discount'];
            $deliveryPrice = $discounts['delivery_price'];

            $nonDiscountPromotion = $promotionService->applyNonDiscountPromotions(
                $order,
                collect($orderItems),
                $data['promotion_id'] ?? null,
                $subtotalBeforeDiscount,
                $user->id,
                false
            );

            if (!empty($nonDiscountPromotion['free_shipping'])) {
                $deliveryPrice = 0;
                $discounts['delivery_price'] = 0;
            }

            $basketDiscount = $this->calculateBasketDiscount(
                $basketDiscountPercent,
                $subtotalBeforeDiscount,
                $externalDiscount
            );

            $finalTotal = $this->calculateFinalTotal(
                $subtotalAfterProductDiscount,
                $basketDiscount,
                $externalDiscount,
                $deliveryPrice
            );

            $affiliateData = $this->resolveAffiliateData(
                $data,
                $discounts['coupon'] ?? null,
                $orderItems,
                $finalTotal
            );

            $this->updateOrderTotals(
                $order,
                $discounts,
                $basketDiscount,
                $subtotalBeforeDiscount,
                $totalQuantity,
                $deliveryPrice,
                $finalTotal,
                $data['promotion_id'] ?? null,
                $affiliateData
            );

            $this->maybeIncrementRecipeOrdersCount($order, $data);


            $this->dispatchOrderCreatedEvent($order);

            return new OneResource($order->fresh('items'));
        });
    }

    /**
     * PREVIEW ORDER
     */
    public function preview(array $data): array
    {
        $user = $this->resolveUser();

        [$basketDiscountPercent, $deliveryPrice] =
            $this->resolveBasketAndDelivery(null, $data);

        [
            $subtotalBeforeDiscount,
            $subtotalAfterProductDiscount,
            $totalQuantity,
            $orderItems
        ] = $this->addItemsToOrder(null, $data);
        $promotionService = app(PromotionService::class);
        // خصومات عامة + نقاط + اشتراك
        $discounts = $this->applyExternalDiscounts(
            null,
            $user,
            $data,
            $orderItems,
            $subtotalBeforeDiscount,
            $deliveryPrice
        );

        unset($discounts['coupon']);

        $externalDiscount = $discounts['total_discount'];
        $deliveryPrice = $discounts['delivery_price'];

        $nonDiscountPromotion = $promotionService->applyNonDiscountPromotions(
            null,
            collect($orderItems),
            $data['promotion_id'] ?? null,
            $subtotalBeforeDiscount,
            $user->id,
            true
        );

        if (!empty($nonDiscountPromotion['free_shipping'])) {
            $deliveryPrice = 0;
            $discounts['delivery_price'] = 0;
        }

        $basketDiscount = $this->calculateBasketDiscount(
            $basketDiscountPercent,
            $subtotalBeforeDiscount,
            $externalDiscount
        );

        $finalTotal =
            $subtotalAfterProductDiscount - $basketDiscount - $externalDiscount + $deliveryPrice;
        $availablePromotions = $promotionService
            ->getAvailablePromotions($subtotalBeforeDiscount, collect($orderItems));

        $discounts['basketDiscount'] = $basketDiscount;

        $formattedDiscounts = array_map(function ($value) {
            if (is_numeric($value)) {
                return $this->convertFormattedPrice($value);
            }
            return $value;
        }, $discounts);

        $formattedItems = array_map(function ($item) {
            return [
                "shop_product_variant_id" => $item["shop_product_variant_id"],
                "product_name" => $item["product_name"],
                "product_image" => $item["product_image"] ?? null,
                "quantity" => $item["quantity"],
                "unit_price" => $this->convertFormattedPrice($item["unit_price"]),
                "final_price" => $this->convertFormattedPrice($item["final_price"]),
                "subtotal" => $this->convertFormattedPrice($item["subtotal"]),
                "extras_total" => $this->convertFormattedPrice($item["extras_total"]),
                "total" => $this->convertFormattedPrice($item["total"]),
                "variant" => $item["variant"],
            ];
        }, $orderItems->toArray());

        return [
            'discounts' => $formattedDiscounts,
            'subtotal_before_discount' => $this->convertFormattedPrice($subtotalBeforeDiscount),
            'subtotal_after_product_discount' => $this->convertFormattedPrice($subtotalAfterProductDiscount),
            'total_quantity' => $totalQuantity,
            'total' => $this->convertFormattedPrice($finalTotal),
            'available_promotions' => $availablePromotions,
            'non_discount_promotions' => $nonDiscountPromotion,
            'excluded_items' => $discounts['excluded_items'] ?? [],
            'orderItems' => $formattedItems
        ];
    }

    /**
     * USER
     */
    private function resolveUser(): User
    {
        return auth('user')->user() ?? User::findOrFail(1);
    }

    private function resolveAddress(User $user, int $addressId)
    {
        return $user->addresses()->findOrFail($addressId);
    }

    private function createOrder(User $user, $address, array $data): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'user_address_id' => $address->id,
            'cart_type' => $data['cart_type'] ?? CartType::DEFAULT->value,
            'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
            'status' => OrderStatus::PENDING->value,
            'payment_method_id' => $this->resolvePaymentMethodId($data),
        ]);
    }

    private function resolvePaymentMethodId(array $data): int
    {
        if (!empty($data['payment_method_id'])) {
            $paymentMethod = PaymentMethod::query()
                ->active()
                ->find($data['payment_method_id']);

            if (!$paymentMethod) {
                throw new CustomExceptionWithMessage('طريقة الدفع المختارة غير متاحة حالياً', 422);
            }

            return (int) $paymentMethod->id;
        }

        $defaultPaymentMethod = PaymentMethod::resolveDefault();

        if (!$defaultPaymentMethod) {
            throw new CustomExceptionWithMessage('لا توجد بوابة دفع افتراضية مفعلة في النظام', 422);
        }

        return (int) $defaultPaymentMethod->id;
    }

    private function applyExternalDiscounts(
        ?Order $order,
        User $user,
        array $data,
        $orderItems,
        float $subtotal,
        float $deliveryPrice
    ): array {

        $promotionService = app(PromotionService::class);
        $couponDiscount = 0;
        $pointsDiscount = 0;
        $subscriptionDiscount = 0;
        $useSubscriptionFreeDelivery = false;
        $promotionDiscount = 0;
        $freeDeliveryFromPoints = false;
        $totalDiscount = 0;

        $isPreview = !$order;
        $excludedItems = [];
        $couponModel = null;

        /**
         * COUPON
         */
        if (!empty($data['coupon'])) {

            [$code, $couponDiscount, $excludedItems, $couponModel] =
                $this->applyCoupon($order, $orderItems, $data['coupon'], 0);

            $totalDiscount += $couponDiscount;
        }

        /**
         * POINTS (preview / create)
         */
        $points = $this->handlePointExchanges(
            $user->id,
            $data,
            $deliveryPrice,
            !$isPreview // apply only when create
        );

        $pointsDiscount = $points['coupon_discount'];
        $freeDeliveryFromPoints = $points['free_delivery'];
        $totalDiscount += $pointsDiscount;

        if ($freeDeliveryFromPoints) {
            $deliveryPrice = 0;
        }

        /**
         * SUBSCRIPTION
         */
        if (!empty($data['use_subscription_discount']) || !empty($data['use_subscription_free_delivery'])) {

            $subscription = app(SubscriptionBenefitsService::class)
                ->applyBenefits(
                    $order,
                    $user->id,
                    $subtotal,
                    $deliveryPrice,
                    !empty($data['use_subscription_discount']),
                    !empty($data['use_subscription_free_delivery'])
                );

            $subscriptionDiscount = $subscription['discount_amount'] ?? 0;
            $totalDiscount += $subscriptionDiscount;

            if ($subscription['free_delivery_applied'] ?? false) {
                $deliveryPrice = 0;
                $useSubscriptionFreeDelivery = true;
            }
        }

        /**
         * PROMOTION
         */
        if (!empty($data['promotion_id'])) {

            $promotionDiscount = $promotionService->applyDiscountPromotion(
                $order,
                $data['promotion_id'],
                $subtotal
            );

            $totalDiscount += $promotionDiscount;
        }

        return [
            'coupon_discount' =>   $couponDiscount,
            'coupon_discount_from_points' => $pointsDiscount,
            'subscription_discount' =>  $subscriptionDiscount,
            'promotion_discount' => $promotionDiscount,
            'total_discount' => $totalDiscount,
            'delivery_price' => $deliveryPrice,
            'useSubscriptionFreeDelivery' => $useSubscriptionFreeDelivery,
            'free_delivery_from_points' => $freeDeliveryFromPoints,
            'coupon' => $couponModel,

            'excluded_items' => $excludedItems, // <--- ترجع الآن بالـ preview
        ];
    }

    private function calculateBasketDiscount(
        float $percent,
        float $subtotal,
        float $externalDiscount
    ): float {

        if ($externalDiscount > 0) {
            return 0;
        }

        return $subtotal * $percent / 100;
    }

    private function calculateFinalTotal(
        float $subtotal,
        float $basketDiscount,
        float $externalDiscount,
        float $delivery
    ): float {

        return round(
            $subtotal - $basketDiscount - $externalDiscount + $delivery,
            2
        );
    }

    private function updateOrderTotals(
        Order $order,
        array $discounts,
        float $basketDiscount,
        float $subtotal,
        int $totalQuantity,
        float $deliveryPrice,
        float $finalTotal,
        ?int $promotionId = null,
        ?array $affiliateData = null
    ): void {

        $order->update([
            'subtotal' => $subtotal,
            'basket_discount' => round($basketDiscount, 2),
            'coupon_discount' => round($discounts['coupon_discount'], 2),
            'coupon_discount_from_points' =>
            round($discounts['coupon_discount_from_points'], 2),
            'free_delivery_from_points' =>
            $discounts['free_delivery_from_points'],
            'subscription_discount' =>
            round($discounts['subscription_discount'], 2),
            'promotion_discount' =>
            round($discounts['promotion_discount'], 2),
            'delivery_price' => $deliveryPrice,
            'total_quantity' => $totalQuantity,
            'total' => $finalTotal,
            'promotion_id' => $promotionId,
            'affiliate_id' => $affiliateData['affiliate_id'] ?? null,
            'affiliate_rate' => $affiliateData['affiliate_rate'] ?? null,
            'affiliate_source' => $affiliateData['affiliate_source'] ?? null,
            'affiliate_commission_type' => $affiliateData['affiliate_commission_type'] ?? null,
            'affiliate_fixed_commission' => $affiliateData['affiliate_fixed_commission'] ?? null,
            'affiliate_commission_amount' => $affiliateData['affiliate_commission_amount'] ?? 0,
        ]);
    }

    private function dispatchOrderCreatedEvent(Order $order): void
    {
        OrderStatusChanged::dispatch(
            $order->fresh('items'),
            null,
            OrderStatus::PENDING->value,
            'user'
        );
    }

    private function maybeIncrementRecipeOrdersCount(Order $order, array $data): void
    {
        $recipeId = $data['recipe_id'] ?? null;
        $cartType = $data['cart_type'] ?? CartType::DEFAULT->value;

        if ($cartType !== CartType::RECIPE->value || !$recipeId) {
            return;
        }

        Recipe::whereKey($recipeId)->increment('orders_count');
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
     * Add items to order / preview
     * ----------------------------- */
    protected function addItemsToOrder($order, $data)
    {
        $subtotalBeforeDiscount = 0;
        $subtotalAfterProductDiscount = 0;
        $totalQuantity = 0;
        $orderItems = collect();

        $isPreview = !$order;

        // تحديد إذا في خصم خارجي
        $hasExternalDiscount = !empty($data['coupon'])
            || !empty($data['point_coupon_exchange_id'])
            || !empty($data['use_subscription_discount'])
            || !empty($data['promotion_id']);

        // تحديد نوع السلة
        $cartType = $data['cart_type'] ?? 'default';
        $hasBasket = $cartType !== CartType::DEFAULT->value;

        foreach ($data['items'] as $item) {

            $shopVariant = ShopProductVariant::with('productVariant.product')
                ->when(!$isPreview, fn($q) => $q->lockForUpdate())
                ->findOrFail($item['shop_product_variant_id']);

            if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                throw new CustomExceptionWithMessage(
                    'custom.orders.insufficient_stock',
                    400,
                    ['product' => $shopVariant->productVariant->product->name]
                );
            }

            $product = $shopVariant->productVariant->product;
            $unitPrice = $shopVariant->price;
            $quantity = $item['quantity'];

            // حساب سعر الـ extras
            $extrasTotal = 0;
            $extrasData = [];

            if (!empty($item['extras']) && is_array($item['extras'])) {
                $extraDetails = \App\Models\ProductExtraDetail::whereIn('id', $item['extras'])
                    ->where('product_id', $product->id)
                    ->get();

                foreach ($extraDetails as $extra) {
                    $extrasTotal += $extra->price;
                    $extrasData[] = [
                        'id' => $extra->id,
                        'detail_key' => $extra->detail_key,
                        'detail_value' => $extra->detail_value,
                        'price' => $extra->price,
                    ];
                }
            }

            $finalPrice = $this->resolveFinalPrice(
                unitPrice: $unitPrice,
                productDiscountPercent: $product->discount,
                hasExternalDiscount: $hasExternalDiscount,
                hasBasket: $hasBasket,
                cartType: $cartType
            );

            $subtotal = $finalPrice * $quantity;
            $total = $subtotal + $extrasTotal;

            if ($order) {
                $orderItem = $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'product_name' => $product->name,
                    'variant_attributes' => $shopVariant->productVariant->getAttributesValuesAttribute(),
                    'product_image' => $product->image_url,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'final_price' => $finalPrice,
                    'subtotal' => $subtotal,
                    'extras_total' => $extrasTotal,
                    'total' => $total,
                ]);

                // حفظ الـ extras
                if (!empty($extrasData)) {
                    foreach ($extrasData as $extra) {
                        $orderItem->extras()->create([
                            'product_extra_detail_id' => $extra['id'],
                            'price' => $extra['price'],
                        ]);
                    }
                }

                app(InventoryService::class)
                    ->decreaseStock($shopVariant->id, $quantity);
            }

            $subtotalBeforeDiscount += $unitPrice * $quantity;
            $subtotalAfterProductDiscount += $total;
            $totalQuantity += $quantity;

            $orderItems->push([
                'shop_product_variant_id' => $shopVariant->id,
                'product_name' => $product->name,
                'product_image' => $product->image_url,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'final_price' => $finalPrice,
                'subtotal' => $subtotal,
                'extras_total' => $extrasTotal,
                'total' => $total,
                'extras' => $extrasData,
                'vendor_id' => $product->vendor_id,
                'category_id' => $product->category_id,
                'product_id' => $product->id,
                'variant' => $shopVariant->productVariant->attributes_values->pluck('name')->toArray()

            ]);
        }


        return [
            $subtotalBeforeDiscount,
            $subtotalAfterProductDiscount,
            $totalQuantity,
            $orderItems
        ];
    }

    protected function resolveFinalPrice(
        float $unitPrice,
        float $productDiscountPercent,
        bool $hasExternalDiscount,
        bool $hasBasket,
        string $cartType
    ): float {
        $shouldApplyProductDiscount =
            !$hasExternalDiscount
            && !$hasBasket
            && $cartType === CartType::DEFAULT->value;

        if (!$shouldApplyProductDiscount) {
            return $unitPrice;
        }

        return $unitPrice * (1 - ($productDiscountPercent / 100));
    }

    protected function applyCoupon($orderOrNull, $basketItemsCollection, $couponCode, $basketDiscount)
    {
        if (!$couponCode) return [null, 0, [], null];

        $coupon = Coupon::valid()
            ->with(['products', 'categories', 'vendors', 'shops'])
            ->where('code', $couponCode)
            ->first();

        if (!$coupon) {
            throw new CustomExceptionWithMessage('custom.invalid_coupon');
        }

        $excludedItems = [];

        foreach ($basketItemsCollection as $item) {
            $allowed = true;

            if ($coupon->products->isNotEmpty() && !$coupon->products->contains('id', $item['product_id'])) {
                $allowed = false;
            }

            if ($coupon->categories->isNotEmpty() && !$coupon->categories->contains('id', $item['category_id'])) {
                $allowed = false;
            }

            if ($coupon->vendors->isNotEmpty() && !$coupon->vendors->contains('id', $item['vendor_id'])) {
                $allowed = false;
            }

            if ($coupon->shops->isNotEmpty() && !$coupon->shops->contains('id', $item['shop_id'])) {
                $allowed = false;
            }

            if (!$allowed) {
                $excludedItems[] = $item['shop_product_variant_id'];
            }
        }

        $eligibleSubtotal = collect($basketItemsCollection)
            ->whereNotIn('shop_product_variant_id', $excludedItems)
            ->sum(fn($i) => $i['subtotal']);

        $couponDiscountAmount = $coupon->calculateDiscount($eligibleSubtotal);

        return [
            $coupon->code,
            $couponDiscountAmount,
            $excludedItems,
            $coupon
        ];
    }

    private function resolveAffiliateData(array $data, ?Coupon $coupon, $orderItems, float $finalTotal): ?array
    {
        $affiliateId = $data['affiliate_id'] ?? null;
        $affiliateSource = $affiliateId ? 'link' : null;

        if (!empty($coupon?->affiliate_id)) {
            $affiliateId = (string) $coupon->affiliate_id;
            $affiliateSource = 'coupon';
        }

        if (empty($affiliateId)) {
            return null;
        }

        $affiliate = User::query()
            ->with('affiliateProducts:id')
            ->where('affiliate_id', $affiliateId)
            ->where('is_affiliate', true)
            ->where('affiliate_approved', true)
            ->first();

        if (!$affiliate) {
            return null;
        }

        $commissionType = $affiliate->affiliate_commission_type ?? 'percentage_order';
        $rate = $affiliate->affiliate_rate ? (float) $affiliate->affiliate_rate : null;
        $fixedCommission = $affiliate->affiliate_fixed_commission ? (float) $affiliate->affiliate_fixed_commission : null;

        $commissionAmount = 0.0;

        if ($commissionType === 'fixed_per_order') {
            $commissionAmount = (float) ($fixedCommission ?? 0);
        } elseif ($commissionType === 'percentage_selected_products') {
            $eligibleProductIds = $affiliate->affiliateProducts->pluck('id')->all();
            $eligibleTotal = collect($orderItems)
                ->whereIn('product_id', $eligibleProductIds)
                ->sum(fn($item) => (float) $item['total']);

            $commissionAmount = $rate ? ($eligibleTotal * ($rate / 100)) : 0;
        } else {
            $commissionAmount = $rate ? ($finalTotal * ($rate / 100)) : 0;
        }

        return [
            'affiliate_id' => $affiliate->affiliate_id,
            'affiliate_rate' => $rate,
            'affiliate_source' => $affiliateSource,
            'affiliate_commission_type' => $commissionType,
            'affiliate_fixed_commission' => $commissionType === 'fixed_per_order' ? $fixedCommission : null,
            'affiliate_commission_amount' => round($commissionAmount, 2),
        ];
    }
    protected function handlePointExchanges(
        int $userId,
        array $data,
        float $deliveryPrice,
        bool $apply = false
    ): array {

        $result = [
            'coupon_discount' => 0,
            'free_delivery' => false,
            'coupon_exchange_id' => null,
            'free_delivery_exchange_id' => null,
        ];

        try {

            $exchangeService = app(PointExchangeService::class);

            /**
             * Coupon from points
             */
            if (!empty($data['point_coupon_exchange_id'])) {

                $exchange = PointExchange::where('id', $data['point_coupon_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'coupon')
                    ->where('status', 'completed')
                    ->first();

                if ($exchange && $exchangeService->isExchangeValid($exchange)) {

                    $result['coupon_discount'] =
                        $exchange->exchange_data['discount_amount'] ?? 0;

                    $result['coupon_exchange_id'] = $exchange->id;

                    if ($apply) {
                        $exchangeService->markExchangeAsUsed($exchange->id);
                    }
                }
            }

            /**
             * Free delivery
             */
            if (!empty($data['point_free_delivery_exchange_id'])) {

                $exchange = PointExchange::where('id', $data['point_free_delivery_exchange_id'])
                    ->where('user_id', $userId)
                    ->where('exchange_type', 'free_delivery')
                    ->where('status', 'completed')
                    ->first();

                if ($exchange && $exchangeService->isExchangeValid($exchange)) {

                    $result['free_delivery'] = true;
                    $result['free_delivery_exchange_id'] = $exchange->id;

                    if ($apply) {
                        $exchangeService->markExchangeAsUsed($exchange->id);
                    }
                }
            }
        } catch (\Throwable $e) {

            Log::error('Point exchange failed', [
                'error' => $e->getMessage()
            ]);
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

        $inventory = app(InventoryService::class);

        foreach ($order->items as $item) {

            $inventory->increaseStock(
                $item->shop_product_variant_id,
                $item->quantity
            );

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



    public function reorder($orderId)
    {
        $user = auth('user')->user();

        $originalOrder = Order::with('items')->findOrFail($orderId);

        // Ensure the order belongs to the user
        if ($originalOrder->user_id !== $user->id) {
            throw new CustomExceptionWithMessage('custom.orders.not_found', 404);
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
                'affiliate_commission_type' => $originalOrder->affiliate_commission_type,
                'affiliate_fixed_commission' => $originalOrder->affiliate_fixed_commission,
                'affiliate_commission_amount' => $originalOrder->affiliate_commission_amount,
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
                    'price' => $item->unit_price,
                    'unit_price' => $item->unit_price,
                    'final_price' => $item->final_price,
                    'subtotal' => $item->subtotal,
                    'extras_total' => $item->extras_total,
                    'total' => $item->total,
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
