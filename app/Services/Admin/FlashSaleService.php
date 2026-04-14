<?php

namespace App\Services\Admin;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Category;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FlashSaleService extends BaseService
{
    public function __construct(FlashSale $model)
    {
        $this->model = $model;
        $this->resource = \App\Http\Resources\FlashSale\OneResource::class;
        $this->collection = \App\Http\Resources\FlashSale\AllResource::class;
        $this->pagination = true;
    }

    public function createFlashSale(array $data): FlashSale
    {
        $this->validateActivationFlag($data);

        return DB::transaction(function () use ($data) {
            /** @var FlashSale $flashSale */
            $flashSale = $this->model->create([
                'name' => $data['name'],
                'end_date' => $data['end_date'],
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->assignProducts($flashSale, $data);

            return $flashSale->fresh();
        });
    }

    public function updateFlashSale(FlashSale $flashSale, array $data): FlashSale
    {
        $this->validateActivationFlag($data, $flashSale->id);

        return DB::transaction(function () use ($flashSale, $data) {
            $flashSale->update([
                'name' => $data['name'],
                'end_date' => $data['end_date'],
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->assignProducts($flashSale, $data);

            return $flashSale->fresh();
        });
    }

    public function expireFlashSales(): int
    {
        return FlashSale::expired()->update(['is_active' => false]);
    }

    protected function assignProducts(FlashSale $flashSale, array $data): void
    {
        $productIds = collect($data['product_ids'] ?? []);

        if (!empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if ($category) {
                $categoryIds = collect([$category->id])
                    ->merge($category->leafDescendants()->pluck('id'))
                    ->filter()
                    ->unique();

                $productIds = $productIds->merge(
                    Product::whereIn('category_id', $categoryIds)->pluck('id')
                );
            }
        }

        if (!empty($data['vendor_id'])) {
            $productIds = $productIds->merge(
                Product::where('vendor_id', $data['vendor_id'])->pluck('id')
            );
        }

        $productIds = $productIds->filter()->unique()->values();

        Product::where('flash_sale_id', $flashSale->id)->update(['flash_sale_id' => null]);

        if ($productIds->isEmpty()) {
            return;
        }

        Product::whereIn('id', $productIds)->update(['flash_sale_id' => $flashSale->id]);
    }

    protected function validateActivationFlag(array $data, ?int $excludeId = null): void
    {
        if (empty($data['is_active'])) {
            return;
        }

        $query = FlashSale::active();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'is_active' => 'An active flash sale already exists. Finish it before approving another one.',
            ]);
        }
    }
}
