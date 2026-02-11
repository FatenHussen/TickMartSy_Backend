<?php

namespace App\Services\Admin;

use App\Models\Basket;
use App\Services\BaseService;
use App\Http\Resources\Admin\Basket\OneResource;
use App\Http\Resources\Admin\Basket\AllResource;
use App\Services\Base\MediaService;

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
     * Override queryBuilder to filter only non-scheduled baskets
     */
    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter only non-scheduled baskets (is_schedule = 0)
        $query->where('is_schedule', false);

        return parent::queryBuilder($query, $filters, $config);
    }

    /**
     * Create basket with items
     */
    public function create($data)
    {
        $basket = Basket::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'num_varieties' => $data['num_varieties'] ?? 0,
            'offer_ends_at' => $data['offer_ends_at'] ?? null,
            'discount' => $data['discount'] ?? 0,
            'discount_type' => $data['discount_type'] ?? 'percentage',
            'rating' => $data['rating'] ?? 0,
            'num_sold' => $data['num_sold'] ?? 0,
            'is_schedule' => false, // Always false for regular baskets
            'delivery_price' => $data['delivery_price'] ?? 0,
        ]);

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $mediaService = new MediaService();
            $mediaService->upload($basket, $data['image'], 'basket');
        }

        // Handle basket items
        if (isset($data['items']) && is_array($data['items'])) {
            $this->syncBasketItems($basket, $data['items']);
        }

        return new $this->resource($basket->load($this->relations));
    }

    /**
     * Update basket with items
     */
    public function update($id, array $data)
    {
        $basket = Basket::findOrFail($id);

        $basket->update([
            'category_id' => $data['category_id'] ?? $basket->category_id,
            'name' => $data['name'] ?? $basket->name,
            'num_varieties' => $data['num_varieties'] ?? $basket->num_varieties,
            'offer_ends_at' => $data['offer_ends_at'] ?? $basket->offer_ends_at,
            'discount' => $data['discount'] ?? $basket->discount,
            'discount_type' => $data['discount_type'] ?? $basket->discount_type,
            'rating' => $data['rating'] ?? $basket->rating,
            'num_sold' => $data['num_sold'] ?? $basket->num_sold,
            'delivery_price' => $data['delivery_price'] ?? $basket->delivery_price,
        ]);

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $mediaService = new MediaService();
            // Delete old image
            $mediaService->deleteAll($basket, 'basket');
            // Upload new image
            $mediaService->upload($basket, $data['image'], 'basket');
        }

        // Handle basket items
        if (isset($data['items']) && is_array($data['items'])) {
            $this->syncBasketItems($basket, $data['items']);
        }

        return new $this->resource($basket->fresh($this->relations));
    }

    /**
     * Sync basket items
     */
    protected function syncBasketItems(Basket $basket, array $items)
    {
        // Delete existing items
        $basket->items()->delete();

        // Create new items
        foreach ($items as $item) {
            $basket->items()->create([
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'shop_product_variant_id' => $item['shop_product_variant_id'] ?? null,
                'shop_product_variant_ids' => $item['shop_product_variant_ids'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'is_required' => $item['is_required'] ?? false,
                'is_extra' => $item['is_extra'] ?? false,
                'min_quantity' => $item['min_quantity'] ?? 1,
                'max_quantity' => $item['max_quantity'] ?? 10,
                'price' => $item['price'],
            ]);
        }

        // Update num_varieties
        $basket->update([
            'num_varieties' => $basket->items()->where('is_extra', false)->count(),
        ]);
    }

    /**
     * Delete basket
     */
    public function delete($id)
    {
        $basket = Basket::findOrFail($id);
        
        // Delete image
        $mediaService = new MediaService();
        $mediaService->deleteAll($basket, 'basket');
        
        // Delete basket (items will be deleted automatically due to cascade)
        $basket->delete();

        return true;
    }
}