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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserBasketScheduleService extends BaseService
{
    protected $model = UserBasketSchedule::class;

    protected $resource = OneResource::class;

    protected $collection = AllResource::class;

    protected $relations = [
        'schedule',
        'schedule.badges',
        'schedule.scheduleImages',
        'items.product.media',
        'items.product.brand',
        'items.product.unitOption',
        'items.variant.productVariant',
        'items.variant.shop',
    ];

    protected $searchableFields = ['id', 'name'];

    protected $sortableFields = ['id', 'created_at'];

    public function getAll($filters = [], $config = [])
    {
        $query = $this->model::query()
            ->with($this->relations)
            ->where('user_id', auth('user')->id())
            ->where('is_draft', false)
            ->latest();

        $perPage = $config['per_page'] ?? 10;
        $page = $config['page'] ?? 1;
        $result = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($result->items())
            ->map(fn (UserBasketSchedule $basket) => $this->decorateBasket($basket));

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

        $basket = $this->model::with($this->relations)
            ->where('user_id', auth('user')->id())
            ->where('is_draft', false)
            ->find($id);

        if (! $basket) {
            Log::warning('Basket not found', [
                'id' => $id,
                'user_id' => auth('user')->id(),
            ]);
            throw new NotFoundException;
        }

        return new $this->resource($this->decorateBasket($basket));
    }

    public function create($data)
    {
        $data['user_id'] = auth('user')->id();
        $data['is_draft'] = false;
        $data['is_active'] = $data['is_active'] ?? true;
        $items = $data['items'] ?? [];
        unset($data['items']);

        $basket = DB::transaction(function () use ($data, $items) {
            $items = $this->normalizeItems($items);
            $basket = $this->model::create($data);

            foreach ($items as $item) {
                $basket->items()->create($item);
            }

            return $basket;
        }, 3);

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
    }

    public function update($id, array $data)
    {
        $basket = DB::transaction(function () use ($id, $data) {
            $basket = $this->model::where('user_id', auth('user')->id())
                ->where('is_draft', false)->lockForUpdate()->find($id);

            if (! $basket) {
                throw new NotFoundException;
            }

            $items = array_key_exists('items', $data) ? $this->normalizeItems($data['items'], true) : null;
            $existingItems = $basket->items()->get()->keyBy('id');

            foreach ($items ?? [] as $index => $item) {
                if (isset($item['id']) && ! $existingItems->has($item['id'])) {
                    throw ValidationException::withMessages([
                        "items.$index.id" => ['The selected item does not belong to this basket.'],
                    ]);
                }
            }

            $basket->update(collect($data)->except('items')->toArray());

            // Metadata-only updates keep the existing lines intact.
            if ($items === null) {
                return $basket;
            }

            foreach ($items as $itemData) {
                if (isset($itemData['id'])) {
                    $existingItems[$itemData['id']]->update(collect($itemData)->except('id')->all());
                    $existingItems->forget($itemData['id']);
                } else {
                    $basket->items()->create($itemData);
                }
            }

            foreach ($existingItems as $itemToDelete) {
                $itemToDelete->delete();
            }

            return $basket;
        }, 3);

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
    }

    public function delete($id): bool
    {
        $basket = $this->model::where('user_id', auth('user')->id())->where('is_draft', false)->find($id);

        if (! $basket) {
            throw new NotFoundException;
        }

        $basket->delete();

        return true;
    }

    public function pause($id)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->where('is_draft', false)->find($id);

        if (! $basket) {
            throw new NotFoundException;
        }

        $basket->update(['paused_at' => now()]);

        Log::info('Basket paused', ['id' => $id, 'user_id' => auth('user')->id()]);

        return new $this->resource($this->decorateBasket($basket->load($this->relations)));
    }

    public function resume($id)
    {
        $basket = $this->model::where('user_id', auth('user')->id())->where('is_draft', false)->find($id);

        if (! $basket) {
            throw new NotFoundException;
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

    private function normalizeItems(mixed $items, bool $allowIds = false): array
    {
        Validator::make(['items' => $items], [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer', 'distinct'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.shop_product_variant_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ])->validate();

        $variants = app(PurchasableVariantService::class)->validateItems($items, true);

        return collect($items)->map(function (array $item, $index) use ($variants, $allowIds) {
            $variant = $variants[$index];
            $productId = $variant->productVariant->product_id;

            if (isset($item['product_id']) && (int) $item['product_id'] !== (int) $productId) {
                throw ValidationException::withMessages([
                    "items.$index.product_id" => ['The product does not match the selected shop variant.'],
                ]);
            }

            $payload = [
                'product_id' => $productId,
                'shop_product_variant_id' => $variant->id,
                'quantity' => (int) $item['quantity'],
            ];

            if ($allowIds && isset($item['id'])) {
                $payload['id'] = (int) $item['id'];
            }

            return $payload;
        })->all();
    }
}
