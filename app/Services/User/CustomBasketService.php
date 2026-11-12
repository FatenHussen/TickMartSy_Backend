<?php

namespace App\Services\User;

use App\Exceptions\NotFoundException;
use App\Http\Resources\UserBasketSchedule\OneResource;
use App\Models\Schedule;
use App\Models\UserBasketSchedule;
use App\Models\UserBasketScheduleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomBasketService
{
    protected array $itemRelations = [
        'schedule.badges',
        'schedule.scheduleImages',
        'items.product.media',
        'items.product.brand',
        'items.product.unitOption',
        'items.variant.productVariant',
        'items.variant.shop',
    ];

    public function show(int $scheduleId): OneResource
    {
        $draft = $this->firstOrCreateDraft($scheduleId);

        return new OneResource($this->loadDraft($draft));
    }

    public function addItem(int $scheduleId, array $data): OneResource
    {
        $draft = DB::transaction(function () use ($scheduleId, $data) {
            $draft = $this->firstOrCreateDraft($scheduleId);
            $draft = $this->findDraft($scheduleId);
            $payload = $this->resolveItemPayload($data);

            $existing = $draft->items()
                ->where('shop_product_variant_id', $payload['shop_product_variant_id'])
                ->lockForUpdate()
                ->first();

            $items = $draft->items()->where('shop_product_variant_id', '!=', $payload['shop_product_variant_id'])
                ->get(['shop_product_variant_id', 'quantity'])->toArray();
            $items[] = $payload;
            app(PurchasableVariantService::class)->validateItems($items, true);

            if ($existing) {
                $existing->update($payload);
            } else {
                $draft->items()->create($payload);
            }

            return $draft;
        });

        return new OneResource($this->loadDraft($draft));
    }

    public function updateItem(int $scheduleId, int $itemId, array $data): OneResource
    {
        $draft = DB::transaction(function () use ($scheduleId, $itemId, $data) {
            $draft = $this->findDraft($scheduleId);
            $item = $draft->items()->whereKey($itemId)->lockForUpdate()->first();

            if (! $item) {
                throw new NotFoundException;
            }

            $payload = $this->resolveItemPayload([
                'shop_product_variant_id' => $item->shop_product_variant_id,
                'quantity' => $data['quantity'] ?? null,
            ]);
            $items = $draft->items()->whereKeyNot($itemId)
                ->get(['shop_product_variant_id', 'quantity'])->toArray();
            $items[] = $payload;
            app(PurchasableVariantService::class)->validateItems($items, true);
            $item->update($payload);

            return $draft;
        });

        return new OneResource($this->loadDraft($draft));
    }

    public function removeItem(int $scheduleId, int $itemId): OneResource
    {
        $draft = $this->findDraft($scheduleId);
        $item = $draft->items()->whereKey($itemId)->first();

        if (! $item) {
            throw new NotFoundException;
        }

        $item->delete();

        return new OneResource($this->loadDraft($draft));
    }

    public function confirm(int $scheduleId, array $data): array
    {
        return DB::transaction(function () use ($scheduleId, $data) {
            $draft = $this->findDraft($scheduleId);
            $draft = $this->loadDraft($draft);

            if ($draft->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['أضيف منتج واحد على الأقل للسلة'],
                ]);
            }

            $confirmSchedule = (bool) $data['confirm_schedule'];
            $cartItems = $draft->items->map(fn (UserBasketScheduleItem $item) => [
                'shop_product_variant_id' => $item->shop_product_variant_id,
                'quantity' => (int) $item->quantity,
            ])->values()->all();
            app(PurchasableVariantService::class)->validateItems($cartItems, true);

            if (! $confirmSchedule) {
                $preview = (new OneResource($draft))->resolve();
                $draft->items()->delete();
                $draft->delete();

                return [
                    'scheduled' => false,
                    'message' => 'تم تجهيز العناصر للسلة لمرة واحدة',
                    'cart_items' => $cartItems,
                    'basket' => $preview,
                ];
            }

            $draft->update([
                'is_draft' => false,
                'is_active' => true,
                'start_date' => $data['start_date'],
                'name' => $draft->name ?: ($draft->schedule?->name ?? 'سلتي المخصصة'),
            ]);

            $confirmed = $this->loadDraft($draft->fresh());

            return [
                'scheduled' => true,
                'message' => 'تم حفظ السلة ضمن طلباتك المجدولة',
                'cart_items' => $cartItems,
                'next_run_date' => $confirmed->start_date?->format('Y-m-d'),
                'basket' => new OneResource($confirmed),
            ];
        });
    }

    protected function firstOrCreateDraft(int $scheduleId): UserBasketSchedule
    {
        $schedule = Schedule::query()->where('is_active', true)->find($scheduleId);

        if (! $schedule) {
            throw new NotFoundException;
        }

        return UserBasketSchedule::query()->firstOrCreate(
            [
                'user_id' => auth('user')->id(),
                'schedule_id' => $schedule->id,
                'is_draft' => true,
            ],
            [
                'name' => $schedule->name,
                'is_active' => false,
            ]
        );
    }

    protected function findDraft(int $scheduleId): UserBasketSchedule
    {
        $draft = UserBasketSchedule::query()
            ->where('user_id', auth('user')->id())
            ->where('schedule_id', $scheduleId)
            ->where('is_draft', true)
            ->lockForUpdate()
            ->first();

        if (! $draft) {
            throw new NotFoundException;
        }

        return $draft;
    }

    protected function loadDraft(UserBasketSchedule $draft): UserBasketSchedule
    {
        return $draft->load($this->itemRelations);
    }

    protected function resolveItemPayload(array $data): array
    {
        $shopVariant = app(PurchasableVariantService::class)->resolve(
            $data['shop_product_variant_id'] ?? null,
            $data['quantity'] ?? null,
            lockForUpdate: true,
        );

        return [
            'product_id' => $shopVariant->productVariant->product_id,
            'shop_product_variant_id' => $shopVariant->id,
            'quantity' => (int) $data['quantity'],
        ];
    }
}
