<?php

namespace App\Services\Admin;

use App\Models\Basket;
use App\Services\BaseService;
use App\Http\Resources\Admin\Basket\OneResource;
use App\Http\Resources\Admin\Basket\AllResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BasketService extends BaseService
{
    protected $model = Basket::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = [
        'category',
        'items.product',
        'items.variant',
        'items.shopProductVariant',
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

    protected $syncRelations = [
        'badges'   => 'badges',
    ];


    /**
     * Override queryBuilder to filter only non-scheduled baskets
     */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter only non-scheduled baskets (is_schedule = 0)
        $query->where('is_schedule', false);

        return parent::queryBuilder($query, $filters, $config);
    }

    /**
     * Override create to handle items
     */
    public function create($data)
    {
        // Store items temporarily
        $items = $data['items'] ?? [];

        // Remove items from data so BaseService doesn't try to create it
        unset($data['items']);

        // Set defaults
        $data['num_varieties'] = 0;
        $data['rating'] = 0;
        $data['num_sold'] = 0;
        $data['is_schedule'] = false;

        // Create basket first
        $basket = $this->model::create($data);

        // Handle single images (for basket image)
        $this->handleSingleImages($basket, $data);

        // Sync items if they exist
        if (!empty($items)) {
            $this->syncBasketItems($basket, $items);
        }

        // Return fresh resource with relations
        $basket = $this->model::with($this->relations)->findOrFail($basket->id);
        return new $this->resource($basket);
    }

    /**
     * Override update to handle items
     */
    public function update($id, array $data)
    {
        Log::info('BasketService::update called', [
            'id' => $id,
            'data_keys' => array_keys($data),
            'has_items' => isset($data['items']),
            'items_count' => isset($data['items']) ? count($data['items']) : 0,
            'items' => $data['items'] ?? null,
        ]);

        // Store items temporarily
        $items = $data['items'] ?? [];

        // Remove items from data so BaseService doesn't try to update it
        unset($data['items']);

        // Find basket
        $basket = $this->model::findOrFail($id);

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

        // Sync items if they exist
        if (!empty($items)) {
            Log::info('Syncing basket items', ['items' => $items]);
            $this->syncBasketItems($basket, $items);
        } else {
            Log::warning('No items to sync');
        }

        // Return fresh resource with relations
        $basket = $this->model::with($this->relations)->findOrFail($basket->id);

        Log::info('Basket after update', [
            'id' => $basket->id,
            'items_count' => $basket->items->count(),
            'calculated_price' => $basket->calculated_price,
        ]);

        return new $this->resource($basket);
    }

    /**
     * Sync basket items - automatically fetch product/variant/price from shop_product_variant
     */
    protected function syncBasketItems(Basket $basket, array $items)
    {
        Log::info('syncBasketItems called', [
            'basket_id' => $basket->id,
            'items_count' => count($items),
            'items' => $items,
        ]);

        // Delete existing items
        $deletedCount = $basket->items()->delete();
        Log::info('Deleted existing items', ['count' => $deletedCount]);

        // Create new items
        foreach ($items as $index => $item) {
            Log::info("Processing item {$index}", ['item' => $item]);

            try {
                // Get shop product variant to extract data
                $shopVariant = \App\Models\ShopProductVariant::with('productVariant.product')
                    ->findOrFail($item['shop_product_variant_id']);

                Log::info("Found shop variant", [
                    'shop_variant_id' => $shopVariant->id,
                    'product_id' => $shopVariant->productVariant->product_id,
                    'variant_id' => $shopVariant->product_variant_id,
                    'price' => $shopVariant->price,
                ]);

                $createdItem = $basket->items()->create([
                    'product_id' => $shopVariant->productVariant->product_id,
                    'variant_id' => $shopVariant->product_variant_id,
                    'shop_product_variant_id' => $item['shop_product_variant_id'],
                    'shop_product_variant_ids' => $item['shop_product_variant_ids'] ?? null,
                    'quantity' => $item['quantity'],
                    'is_required' => true, // Always required
                    'is_extra' => false, // Never extra for regular items
                    'min_quantity' => 1, // Default
                    'max_quantity' => 10, // Default
                    'price' => $shopVariant->price, // Get price from shop variant
                ]);

                Log::info("Created basket item", ['item_id' => $createdItem->id]);
            } catch (\Exception $e) {
                Log::error("Error creating basket item", [
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

        Log::info('Basket updated', [
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

        // Delete basket (items will be deleted automatically due to cascade)
        $basket->delete();

        return true;
    }

    /**
     * Override handleRelations to sync basket items
     */
    protected function handleRelations($object, array &$data)
    {
        // Handle basket items
        if (isset($data['items']) && \is_array($data['items'])) {
            $this->syncBasketItems($object, $data['items']);
            unset($data['items']);
        }
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
