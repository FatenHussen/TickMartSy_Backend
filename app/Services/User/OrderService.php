<?php

namespace App\Services\User;

use App\Http\Resources\Order\OneResource;
use App\Http\Resources\Order\AllResource;

use App\Models\Order;
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
    public function create($data)
    {

        return DB::transaction(function () use ($data) {

            $order = Order::create([
                'user_id' => 1, // أو auth('user')->id()
                'user_address_id' => $data['address_id'],
                'cart_type' => $data['cart_type'] ?? 'default',
                'is_instant_delivery' => $data['is_instant_delivery'] ?? false,
                'order_status' => 'pending',
            ]);

            $subtotal = 0;
            $numItems = 0;

            foreach ($data['items'] as $item) {

                $shopVariant = ShopProductVariant::with([
                    'productVariant.product'
                ])
                    ->lockForUpdate()
                    ->findOrFail($item['shop_product_variant_id']);


                if (!is_null($shopVariant->quantity) && $shopVariant->quantity < $item['quantity']) {
                    throw new Exception("Insufficient stock for product ID {$shopVariant->productVariant->product->id}");
                }

                $price = $shopVariant->price;
                $quantity = $item['quantity'];

                $productName = $shopVariant->productVariant->product->name;

                $variantAttributes = $shopVariant->productVariant->getAttributesValuesAttribute()->map(fn($value) => [
                    'attribute' => $value->categoryAttribute?->name ?? 'N/A',
                    'value' => $value->name ?? 'N/A',
                ]);


                $order->items()->create([
                    'shop_product_variant_id' => $shopVariant->id,
                    'product_name' => $productName,
                    'variant_attributes' => $variantAttributes,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $shopVariant->productVariant->product->discount
                ]);

                if (!is_null($shopVariant->quantity)) {
                    $shopVariant->decrement('quantity', $quantity);
                }

                $subtotal += $price * $quantity;
                $numItems += $quantity;
            }

            $order->update([
                'total' => $subtotal,
                'total_quantity' => $numItems,
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
