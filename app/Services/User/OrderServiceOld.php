<?php

namespace App\Services\User;

use App\Enums\CartType;
use App\Enums\OrderStatus;
use App\Http\Resources\Order\OneResource;
use App\Http\Resources\Order\AllResource;
use App\Models\Basket;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\ShopProductVariant;
use App\Models\User;
use App\Services\BaseService;
use App\Services\User\CalculateDeliveryPriceService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderServiceOld extends BaseService
{
    public function __construct(Order $model)
    {
        $this->model            = $model;
        $this->resource         = OneResource::class;
        $this->collection       = AllResource::class;
        $this->searchableFields = ['total', 'total_quantity'];
        $this->sortableFields   = ['id', 'total', 'total_quantity'];
        $this->relations        = ['items'];
        $this->pagination       = true;
    }

    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] = 1; // auth('user')->id()
        return parent::getAll($filters, $config);
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {

            /** @var User $user */
            $user = auth('user')->user() ?? User::find(1);

            Log::info('Order creation started', [
                'user_id' => $user->id,
                'cart_type' => $data['cart_type'] ?? CartType::DEFAULT->value,
                'address_id' => $data['address_id'],
            ]);

            $subtotalBeforeDiscount       = 0;
            $subtotalAfterProductDiscount = 0;
            $totalQuantity                = 0;
            $basketDiscount               = 0;
            $deliveryPrice                = 0;

            /** -----------------------------
             * Create Order
             * ----------------------------- */
            $order = Order::create([
                'user_id'             => $user->id,
                'user_address_id'     => $data['address_id'],
                'cart_type'           => $data['cart_type'] ?? CartType::DEFAULT->value,
                'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
                'order_status'        => OrderStatus::PENDING->value,
            ]);

            Log::info('Order created', [
                'order_id' => $order->id,
            ]);

            /** -----------------------------
             * Determine Discounts & Delivery
             * ----------------------------- */

            if ($order->cart_type === CartType::RECIPE->value) {
                $basket = Recipe::findOrFail($data['recipe_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
            }

            if ($order->cart_type === CartType::ADMIN_CART->value) {
                $basket = Basket::findOrFail($data['admin_basket_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
            }

            if ($order->cart_type === CartType::SCHEDULE_ADMIN_CART->value) {
                $basket = Basket::findOrFail($data['admin_schedule_basket_id']);
                $basketDiscount = $basket->discount;
                $deliveryPrice  = $basket->delivery_price;
            }
            if ($order->cart_type === CartType::DEFAULT->value) {

                Log::info('Calculating delivery price', [
                    'order_id' => $order->id,
                ]);

                $variantIds = collect($data['items'])
                    ->pluck('shop_product_variant_id')
                    ->values()
                    ->toArray();

                $deliveryPrice = CalculateDeliveryPriceService::handle(
                    user: $user,
                    items: $variantIds,
                    addressId: $data['address_id']
                );
            }

            Log::info('Basket & delivery resolved', [
                'order_id' => $order->id,
                'basket_discount' => $basketDiscount,
                'delivery_price' => $deliveryPrice,
            ]);

            /** -----------------------------
             * Add Items
             * ----------------------------- */
            Log::info('Adding items started', [
                'order_id' => $order->id,
                'items_count' => count($data['items']),
            ]);

            foreach ($data['items'] as $item) {

                $shopVariant = ShopProductVariant::with('productVariant.product')
                    ->lockForUpdate()
                    ->findOrFail($item['shop_product_variant_id']);

                if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                    Log::warning('Insufficient stock', [
                        'order_id' => $order->id,
                        'shop_product_variant_id' => $shopVariant->id,
                        'available' => $shopVariant->quantity,
                        'requested' => $item['quantity'],
                    ]);
                    throw new Exception('Insufficient stock');
                }

                $product  = $shopVariant->productVariant->product;
                $price    = $shopVariant->price;
                $quantity = $item['quantity'];

                $productDiscount = 0;
                $priceAfterDiscount = $price;

                if ($order->cart_type === CartType::DEFAULT->value) {
                    $productDiscount    = $product->discount;
                    $priceAfterDiscount = $price * (1 - ($productDiscount / 100));
                }

                $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'product_name' => $product->name,
                    'variant_attributes' => $shopVariant->productVariant
                        ->getAttributesValuesAttribute(),
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $productDiscount,
                ]);

                if (!is_null($shopVariant->quantity)) {
                    $shopVariant->decrement('quantity', $quantity);
                }

                $subtotalBeforeDiscount       += $price * $quantity;
                $subtotalAfterProductDiscount += $priceAfterDiscount * $quantity;
                $totalQuantity                += $quantity;

                Log::info('Item added', [
                    'order_id' => $order->id,
                    'product' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);
            }

            /** -----------------------------
             * Totals
             * ----------------------------- */
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscount / 100);
            $finalTotal = $subtotalAfterProductDiscount - $basketDiscountAmount;

            $order->update([
                'delivery_price'   => $deliveryPrice,
                'subtotal'        => round($subtotalBeforeDiscount, 2), //sum without any discount
                'total_quantity'  => $totalQuantity,
                'basket_discount' => $basketDiscount,
                // 'discount_source' => $order->cart_type,
                'total'           => round($finalTotal, 2),
            ]);

            return new $this->resource($order->load('items'));
        });
    }
}
