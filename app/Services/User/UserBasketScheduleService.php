<?php

namespace App\Services\User;

use App\Exceptions\NotFoundException;
use App\Http\Resources\UserBasketSchedule\AllResource;
use App\Http\Resources\UserBasketSchedule\OneResource;
use App\Models\ScheduledBasketAlert;
use App\Models\UserBasketSchedule;
use App\Services\BaseService;
use App\Services\ScheduledBasketAlertService;
use App\Services\ScheduledBasketAvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserBasketScheduleService extends BaseService
{
    protected $model = UserBasketSchedule::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'schedule',
        'items.product',
        'items.variant',
    ];

    protected $searchableFields = ['id', 'name'];
    protected $sortableFields = ['id', 'created_at'];

    public function getAll($filters = [], $config = [])
    {
        $query = $this->model::query()
            ->with($this->relations)
            ->where('user_id', auth('user')->id())
            ->latest();

        $perPage = $config['per_page'] ?? 10;
        $page = $config['page'] ?? 1;
        $result = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($result->items())
            ->map(fn(UserBasketSchedule $basket) => $this->decorateBasket($basket));

        return [
            'items' => AllResource::collection($items),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
        ];
    }

    public function query(array $filters)
    {
        return UserBasketSchedule::query()->latest();
    }

    public function getOne($id)
    {
        Log::info('UserBasketScheduleService::getOne', [
            'id' => $id,
            'user_id' => auth('user')->id(),
        ]);

        $basket = $this->model::with($this->relations)->find($id);

        if (!$basket) {
            Log::warning('Basket not found', [
                'id' => $id,
                'user_id' => auth('user')->id(),
            ]);
            throw new NotFoundException();
        }

        return new $this->resource($this->decorateBasket($basket));
    }

    public function create($data)
    {
        $data['user_id'] = auth('user')->id();
        $items = $data['items'] ?? [];
        unset($data['items']);

        $basket = DB::transaction(function () use ($data, $items) {
            $basket = $this->model::create($data);

            foreach ($items as $item) {
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

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
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

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
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

    public function pause($id)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->find($id);

        if (!$basket) {
            throw new NotFoundException();
        }

        $basket->update(['paused_at' => now()]);

        Log::info('Basket paused', ['id' => $id, 'user_id' => auth('user')->id()]);

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
    }

    public function resume($id)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->find($id);

        if (!$basket) {
            throw new NotFoundException();
        }

        $basket->update(['paused_at' => null]);

        Log::info('Basket resumed', ['id' => $id, 'user_id' => auth('user')->id()]);

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
    }

    private function decorateBasket(UserBasketSchedule $basket): UserBasketSchedule
    {
        $availabilityService = app(ScheduledBasketAvailabilityService::class);
        $alertService = app(ScheduledBasketAlertService::class);

        $summary = $availabilityService->evaluateUserSchedule($basket);

        $basket->availability_summary = $summary;
        $basket->availability_items_by_id = $summary['items_by_id'];
        $basket->active_alert = $alertService->getOpenAlert(
            ScheduledBasketAlert::BASKET_TYPE_USER_SCHEDULE,
            $basket->user_id,
            $basket->id,
        );

        return $basket;
    }
}
