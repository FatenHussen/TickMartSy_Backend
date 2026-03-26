<?php

namespace App\Services\User;

use App\Models\UserBasketSchedule;
use App\Exceptions\NotFoundException;
use App\Http\Resources\UserBasketSchedule\AllResource;
use App\Http\Resources\UserBasketSchedule\OneResource;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;

class UserBasketScheduleService extends BaseService
{
    protected $model = UserBasketSchedule::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'schedule',
        // 'category',
        'items.product',
        'items.variant',
    ];

    protected $searchableFields = ['id', 'name'];
    protected $sortableFields  = ['id', 'created_at'];

    public function getAll($filters = [], $config = [])
    {
        $query = $this->model::where('user_id', auth('user')->id())->active();

        return parent::getAll($filters, $config, $query);
    }
    public function query(array $filters)
    {
        $query = UserBasketSchedule::query()->latest();

        return $query;
    }

    public function getOne($id)
    {
        Log::info('UserBasketScheduleService::getOne', [
            'id' => $id,
            'user_id' => auth('user')->id(),
        ]);

        $basket = $this->model::with($this->relations)
            //->where('user_id', auth('user')->id())
            ->find($id);

        Log::info('Basket found', [
            'basket' => $basket ? $basket->id : null,
        ]);

        if (!$basket) {
            Log::warning('Basket not found', [
                'id' => $id,
                'user_id' => auth('user')->id(),
            ]);
            throw new NotFoundException();
        }

        return new $this->resource($basket);
    }

    public function create($data)
    {
        $data['user_id'] = auth('user')->id();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $basket = DB::transaction(function () use ($data, $items) {
            $basket = $this->model::create($data);

            foreach ($items as $item) {
                // Get product_id from shop_product_variant_id if not provided
                if (!isset($item['product_id']) && isset($item['shop_product_variant_id'])) {
                    $shopVariant = \App\Models\ShopProductVariant::with('productVariant.product')
                        ->find($item['shop_product_variant_id']);

                    if ($shopVariant) {
                        $item['product_id'] = $shopVariant->productVariant->product_id;
                    }
                }

                $basket->items()->create($item);
            }

            return $basket;
        });

        return new $this->resource($basket->load($this->relations));
    }



    public function update($id, array $data)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->find($id);

        if (!$basket) {
            throw new NotFoundException();
        }

        $basket->update(collect($data)->except('items')->toArray());

        $items = $data['items'] ?? [];

        $existingItems = $basket->items()->get()->keyBy('id');

        foreach ($items as $itemData) {
            // Get product_id from shop_product_variant_id if not provided
            if (!isset($itemData['product_id']) && isset($itemData['shop_product_variant_id'])) {
                $shopVariant = \App\Models\ShopProductVariant::with('productVariant.product')
                    ->find($itemData['shop_product_variant_id']);

                if ($shopVariant) {
                    $itemData['product_id'] = $shopVariant->productVariant->product_id;
                }
            }

            if (isset($itemData['id']) && $existingItems->has($itemData['id'])) {
                $existingItems[$itemData['id']]->update($itemData);
                $existingItems->forget($itemData['id']);
            } else {
                $basket->items()->create($itemData);
            }
        }

        foreach ($existingItems as $itemToDelete) {
            $itemToDelete->delete();
        }

        return new $this->resource($basket->load($this->relations));
    }



    public function delete($id): bool
    {
        $basket = $this->model::where('user_id', auth('user')->id())->find($id);

        if (!$basket) {
            throw new NotFoundException();
        }

        $basket->delete();

        return true;
    }

    /**
     * Pause a scheduled basket
     */
    public function pause($id)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->find($id);

        if (!$basket) {
            throw new NotFoundException();
        }

        $basket->update(['paused_at' => now()]);

        Log::info('Basket paused', ['id' => $id, 'user_id' => auth('user')->id()]);

        return new $this->resource($basket->load($this->relations));
    }

    /**
     * Resume a paused scheduled basket
     */
    public function resume($id)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->find($id);

        if (!$basket) {
            throw new NotFoundException();
        }

        $basket->update(['paused_at' => null]);

        Log::info('Basket resumed', ['id' => $id, 'user_id' => auth('user')->id()]);

        return new $this->resource($basket->load($this->relations));
    }
}
