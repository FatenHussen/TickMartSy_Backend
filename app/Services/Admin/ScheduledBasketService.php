<?php

namespace App\Services\Admin;

use App\Models\Basket;
use App\Services\BaseService;
use App\Http\Resources\Admin\ScheduledBasket\OneResource;
use App\Http\Resources\Admin\ScheduledBasket\AllResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledBasketService extends BaseService
{
    protected $model = Basket::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'categories',
        'items.product',
        'items.variant',
        'items.shopProductVariant',
        'schedules',
        'badges',
    ];

    protected $searchableFields = [
        'name',
        'description',
        'category_id',
        'price',
        'discount',
        'num_sold',
        'is_schedule',
    ];

    protected $sortableFields = [
        'id',
        'name',
        'price',
        'discount',
        'rating',
        'num_sold',
        'created_at',
        'offer_ends_at',
    ];

    /**
     * Override queryBuilder to filter only scheduled baskets
     */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        $categoryId = $filters['category_id'] ?? null;
        unset($filters['category_id']);

        // Filter only scheduled baskets (is_schedule = 1)
        $query->where('is_schedule', true);

        $query = parent::queryBuilder($query, $filters, $config);

        if ($categoryId) {
            $query->where(function ($q) use ($categoryId) {
                $q->whereHas('categories', function ($categoryQuery) use ($categoryId) {
                    $categoryQuery->where('categories.id', $categoryId);
                })->orWhere('category_id', $categoryId);
            });
        }

        return $query;
    }

    /**
     * Override create to handle items and schedule
     */
    public function create($data)
    {
        DB::beginTransaction();
        try {
            $categoryIds = $this->resolveCategoryIds($data);

            // Store items, schedules, and badges temporarily
            $items = $data['items'] ?? [];
            $schedulesData = $data['schedules'] ?? [];
            $badgesData = $data['badges'] ?? [];

            // Remove items, schedules, and badges from data
            unset($data['items'], $data['schedules'], $data['badges']);

            // Set defaults
            if (!empty($categoryIds)) {
                $data['category_id'] = $categoryIds[0];
            }

            $data['num_varieties'] = 0;
            $data['rating'] = 0;
            $data['num_sold'] = 0;
            $data['is_schedule'] = true;

            // Create basket first
            $basket = $this->model::create($data);

            // Handle single images (for basket image)
            $this->handleSingleImages($basket, $data);

            if (!empty($categoryIds)) {
                $basket->categories()->sync($categoryIds);
            }

            // Sync items if they exist
            if (!empty($items)) {
                $this->syncScheduledBasketItems($basket, $items);
            }

            // Create schedules if provided
            if (!empty($schedulesData)) {
                foreach ($schedulesData as $scheduleData) {
                    $basket->schedules()->create([
                        'title' => $scheduleData['title'] ?? ['en' => 'Schedule', 'ar' => 'جدولة'],
                        'number_of_days' => $scheduleData['number_of_days'],
                        'discount_type' => $scheduleData['discount_type'] ?? null,
                        'discount_value' => $scheduleData['discount_value'] ?? null,
                        'is_active' => $scheduleData['is_active'] ?? true,
                        'is_default' => $scheduleData['is_default'] ?? false,
                    ]);
                }
            }

            // Sync badges if provided
            if (!empty($badgesData)) {
                $badgeSync = [];
                foreach ($badgesData as $badge) {
                    if (is_array($badge) && isset($badge['id'])) {
                        $badgeSync[$badge['id']] = [];
                    } elseif (is_numeric($badge)) {
                        $badgeSync[$badge] = [];
                    }
                }
                $basket->badges()->sync($badgeSync);
            }

            DB::commit();

            // Return fresh resource with relations
            $basket = $this->model::with(array_merge($this->relations, ['badges']))->findOrFail($basket->id);
            return new $this->resource($basket);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating scheduled basket', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }


    /**
     * Override update to handle items and schedule
     */
    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $categoryIds = $this->resolveCategoryIds($data);

            Log::info('ScheduledBasketService::update called', [
                'id' => $id,
                'data_keys' => array_keys($data),
                'has_items' => isset($data['items']),
                'items_count' => isset($data['items']) ? count($data['items']) : 0,
            ]);

            // Store items, schedules, and badges temporarily
            $items = $data['items'] ?? [];
            $schedulesData = $data['schedules'] ?? [];
            $badgesData = $data['badges'] ?? [];

            // Remove items, schedules, and badges from data
            unset($data['items'], $data['schedules'], $data['badges']);

            // Find basket
            $basket = $this->model::findOrFail($id);

            if (!empty($categoryIds)) {
                $data['category_id'] = $categoryIds[0];
            }

            // Handle translations if exists
            if (property_exists($basket, 'translatable')) {
                foreach ($basket->translatable as $field) {
                    if (isset($data[$field])) {
                        $basket->setTranslations($field, $data[$field]);
                        unset($data[$field]);
                    }
                }
            }

            // Update basket
            $basket->update($data);

            // Handle single images (for basket image)
            $this->handleSingleImages($basket, $data);

            if (!empty($categoryIds)) {
                $basket->categories()->sync($categoryIds);
            }

            // Sync items if they exist
            if (!empty($items)) {
                $this->syncScheduledBasketItems($basket, $items);
            }

            // Update schedules if provided
            if (!empty($schedulesData)) {
                // Delete old schedules
                $basket->schedules()->delete();

                // Create new schedules
                foreach ($schedulesData as $scheduleData) {
                    $basket->schedules()->create([
                        'title' => $scheduleData['title'] ?? ['en' => 'Schedule', 'ar' => 'جدولة'],
                        'number_of_days' => $scheduleData['number_of_days'],
                        'discount_type' => $scheduleData['discount_type'] ?? null,
                        'discount_value' => $scheduleData['discount_value'] ?? null,
                        'is_active' => $scheduleData['is_active'] ?? true,
                        'is_default' => $scheduleData['is_default'] ?? false,
                    ]);
                }
            }

            // Sync badges if provided
            if (!empty($badgesData)) {
                $badgeSync = [];
                foreach ($badgesData as $badge) {
                    if (is_array($badge) && isset($badge['id'])) {
                        $badgeSync[$badge['id']] = [];
                    } elseif (is_numeric($badge)) {
                        $badgeSync[$badge] = [];
                    }
                }
                $basket->badges()->sync($badgeSync);
            }

            DB::commit();

            // Return fresh resource with relations
            $basket = $this->model::with(array_merge($this->relations, ['badges']))->findOrFail($basket->id);
            return new $this->resource($basket);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating scheduled basket', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function resolveCategoryIds(array &$data): array
    {
        $categoryIds = [];

        if (isset($data['category_ids']) && is_array($data['category_ids'])) {
            $categoryIds = array_values(array_unique(array_map('intval', $data['category_ids'])));
            unset($data['category_ids']);
        } elseif (!empty($data['category_id'])) {
            $categoryIds = [(int) $data['category_id']];
        }

        return array_values(array_filter($categoryIds));
    }


    /**
     * Sync scheduled basket items - handles primary variant + alternatives
     */
    protected function syncScheduledBasketItems(Basket $basket, array $items)
    {
        Log::info('syncScheduledBasketItems called', [
            'basket_id' => $basket->id,
            'items_count' => count($items),
            'items' => $items,
        ]);

        // Delete existing items
        $deletedCount = $basket->items()->delete();
        Log::info('Deleted existing items', ['count' => $deletedCount]);

        // Create new items
        foreach ($items as $index => $item) {
            Log::info("Processing scheduled item {$index}", ['item' => $item]);

            try {
                // Get primary variant (required)
                $primaryVariantId = $item['shop_product_variant_id'] ?? null;

                if (!$primaryVariantId) {
                    Log::warning("Item {$index} has no shop_product_variant_id (primary), skipping");
                    continue;
                }

                // Get primary shop product variant
                $primaryVariant = \App\Models\ShopProductVariant::with('productVariant.product')
                    ->find($primaryVariantId);

                if (!$primaryVariant) {
                    Log::warning("Primary variant not found", ['id' => $primaryVariantId]);
                    continue;
                }

                // Get alternatives (optional)
                $alternativeIds = $item['shop_product_variant_ids'] ?? [];

                Log::info("Creating scheduled basket item", [
                    'product_id' => $primaryVariant->productVariant->product_id,
                    'variant_id' => $primaryVariant->product_variant_id,
                    'primary_variant_id' => $primaryVariantId,
                    'alternative_ids' => $alternativeIds,
                    'price' => $primaryVariant->price,
                ]);

                $createdItem = $basket->items()->create([
                    'product_id' => $primaryVariant->productVariant->product_id,
                    'variant_id' => $primaryVariant->product_variant_id,
                    'shop_product_variant_id' => $primaryVariantId, // Primary variant
                    'shop_product_variant_ids' => $alternativeIds, // Alternative variants (optional)
                    'quantity' => $item['quantity'] ?? 1,
                    'is_required' => $item['is_required'] ?? false,
                    'is_extra' => $item['is_extra'] ?? false,
                    'min_quantity' => $item['min_quantity'] ?? 1,
                    'max_quantity' => $item['max_quantity'] ?? 10,
                    'price' => $primaryVariant->price, // Price from primary variant only
                ]);

                Log::info("Created scheduled basket item", ['item_id' => $createdItem->id]);
            } catch (\Exception $e) {
                Log::error("Error creating scheduled basket item", [
                    'item' => $item,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        // Refresh basket to get updated items
        $basket->refresh();

        Log::info('Basket refreshed', [
            'items_count' => $basket->items()->count(),
            'calculated_price' => $basket->calculated_price,
        ]);

        // Update num_varieties and price
        $basket->update([
            'num_varieties' => $basket->items()->where('is_extra', false)->count(),
            'price' => $basket->calculated_price,
        ]);

        Log::info('Scheduled basket updated', [
            'num_varieties' => $basket->num_varieties,
            'price' => $basket->price,
        ]);
    }

    /**
     * Delete basket
     */
    public function delete($id): bool
    {
        $basket = Basket::findOrFail($id);

        // Delete image if exists
        if ($basket->image && Storage::disk('public')->exists($basket->image)) {
            Storage::disk('public')->delete($basket->image);
        }

        // Delete basket (items and schedules will be deleted automatically due to cascade)
        $basket->delete();

        return true;
    }

    /**
     * Override handleSingleImages to handle basket image
     */
    protected function handleSingleImages($object, array &$data): array
    {
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old image if exists
            if ($object->image && Storage::disk('public')->exists($object->image)) {
                Storage::disk('public')->delete($object->image);
            }

            // Upload new image
            $imagePath = $data['image']->store('baskets', 'public');
            $object->update(['image' => $imagePath]);
            unset($data['image']);
        }

        return [];
    }
}
