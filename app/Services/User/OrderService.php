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
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderService extends BaseService
{

    public function __construct(Order $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->searchableFields = ['total', 'total_quantity'];
        $this->sortableFields   = ['id', 'total', 'total_quantity'];
        $this->relations = ['items'];

        $this->pagination = true;
    }
    public function getAll($filters = [], $config = [])
    {
        $filters['user_id'] = 1; // أو auth('user')->id()
        return parent::getAll($filters, $config);
    }

    public function create($data)
    {
        return DB::transaction(function () use ($data) {

            $subtotalBeforeDiscount = 0;
            $subtotalAfterProductDiscount = 0;
            $totalQuantity = 0;

            Log::info("Data ", ['data' => $data]);

            /** --------------------------------
             * Create Order
             * -------------------------------- */
            $order = Order::create([
                'user_id'             => auth('user')->id() ?? 1,
                'user_address_id'     => $data['address_id'],
                'cart_type'           => $data['cart_type'] ?? CartType::DEFAULT->value,
                'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
                'order_status'        => OrderStatus::PENDING->value,
            ]);

            Log::info("Order created", ['order_id' => $order->id]);

            /** --------------------------------
             * Determine Basket Discount
             * -------------------------------- */
            $basketDiscountPercentage = 0;
            $discountSource = null;

            if ($order->cart_type == CartType::RECIPE->value && !empty($data['recipe_id'])) {
                $basketDiscountPercentage = Recipe::findOrFail($data['recipe_id'])->discount;
                $discountSource = 'recipe';
            }

            if ($order->cart_type == CartType::ADMIN_CART->value && !empty($data['admin_basket_id'])) {
                $basketDiscountPercentage = Basket::findOrFail($data['admin_basket_id'])->discount;
                $discountSource = 'admin_cart';
            }

            if ($order->cart_type == CartType::SCHEDULED_ADMIN_CART->value && !empty($data['admin_basket_id'])) {
                $basketDiscountPercentage = Basket::findOrFail($data['admin_basket_id'])->discount;
                $discountSource = 'scheduled_admin_cart';
            }

            Log::info("Basket discount determined", [
                'discount_percentage' => $basketDiscountPercentage,
                'source' => $discountSource
            ]);

            /** --------------------------------
             * Add Items
             * -------------------------------- */
            foreach ($data['items'] as $item) {

                $shopVariant = ShopProductVariant::with('productVariant.product')
                    ->lockForUpdate()
                    ->findOrFail($item['shop_product_variant_id']);

                if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                    Log::warning("Insufficient stock", [
                        'shop_variant_id' => $shopVariant->id,
                        'available' => $shopVariant->quantity,
                        'requested' => $item['quantity']
                    ]);
                    throw new Exception('Insufficient stock');
                }

                $product  = $shopVariant->productVariant->product;
                $price    = $shopVariant->price;
                $quantity = $item['quantity'];

                $productDiscountPercentage = 0;
                $priceAfterProductDiscount = $price;

                if ($order->cart_type === CartType::DEFAULT->value) {
                    $productDiscountPercentage = $product->discount;
                    $priceAfterProductDiscount = $price * (1 - ($productDiscountPercentage / 100));
                }

                $variantAttributes = $shopVariant->productVariant
                    ->getAttributesValuesAttribute()
                    ->map(fn($value) => [
                        'attribute' => $value->categoryAttribute?->name ?? 'N/A',
                        'value'     => $value->name ?? 'N/A',
                    ]);

                $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'product_name'            => $product->name,
                    'variant_attributes'      => $variantAttributes,
                    'quantity'                => $quantity,
                    'price'                   => $price,
                    'discount'                => $productDiscountPercentage,
                ]);

                Log::info("Item added to order", [
                    'shop_variant_id' => $shopVariant->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $productDiscountPercentage,
                ]);

                if (!is_null($shopVariant->quantity)) {
                    $shopVariant->decrement('quantity', $quantity);
                    Log::info("Shop variant stock decremented", [
                        'shop_variant_id' => $shopVariant->id,
                        'new_quantity' => $shopVariant->quantity
                    ]);
                }

                $subtotalBeforeDiscount += $price * $quantity;
                $subtotalAfterProductDiscount += $priceAfterProductDiscount * $quantity;
                $totalQuantity += $quantity;
            }

            /** --------------------------------
             * Apply Basket Discount
             * -------------------------------- */
            $basketDiscountAmount = $subtotalAfterProductDiscount * ($basketDiscountPercentage / 100);
            $finalTotal = $subtotalAfterProductDiscount - $basketDiscountAmount;

            Log::info("Basket discount applied", [
                'subtotal_after_product_discount' => $subtotalAfterProductDiscount,
                'basket_discount_amount' => $basketDiscountAmount,
                'final_total' => $finalTotal
            ]);

            /** --------------------------------
             * Update Order Totals
             * -------------------------------- */
            $order->update([
                'subtotal'           => round($subtotalBeforeDiscount, 2),
                'total_quantity'     => $totalQuantity,
                'basket_discount'    => $basketDiscountPercentage,
                'discount_source'    => $discountSource,
                'total'              => round($finalTotal, 2),
            ]);

            Log::info("Order totals updated", [
                'subtotal' => $subtotalBeforeDiscount,
                'total_quantity' => $totalQuantity,
                'total' => $finalTotal
            ]);

            return new $this->resource($order->load('items'));
        });
    }


    // public function update(Order $order, array $data): Order
    // {
    //     $order->update($data);

    //     return $order->refresh()->load('items');
    // }
}
