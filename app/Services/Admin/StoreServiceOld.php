<?php

namespace App\Services\Admin;

use App\Models\Store;
use App\Services\Base\MediaService;
use Illuminate\Support\Facades\DB;

class StoreServiceOld
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /* =========================================================
     | List / Find
     ========================================================= */

    public function list()
    {
        return Store::with(['areas', 'services', 'categories', 'media'])->get();
    }

    public function find(int $id): ?Store
    {
        return Store::with(['areas', 'services', 'categories', 'media'])->find($id);
    }

    /* =========================================================
     | Create
     ========================================================= */

    public function create(array $data): Store
    {
        return DB::transaction(function () use ($data) {

            $store = Store::create($data);

            $store->areas()->sync($data['area_ids'] ?? []);
            $store->services()->sync($data['service_ids'] ?? []);
            $store->categories()->sync($data['category_ids'] ?? []);

            if (!empty($data['logo'])) {
                $this->mediaService->upload($store, $data['logo'], 'logo');
            }

            if (!empty($data['cover_images'])) {
                $this->mediaService->uploadMultiple($store, $data['cover_images'], 'cover');
            }

            return $store;
        });
    }

    /* =========================================================
     | Update
     ========================================================= */

    public function update(Store $store, array $data): Store
    {
        return DB::transaction(function () use ($store, $data) {

            $store->update($data);

            $store->areas()->sync($data['area_ids'] ?? []);
            $store->services()->sync($data['service_ids'] ?? []);
            $store->categories()->sync($data['category_ids'] ?? []);

            if (!empty($data['logo'])) {
                $oldLogo = $store->media()->where('collection', 'logo')->first();

                $oldLogo
                    ? $this->mediaService->replace($oldLogo, $data['logo'])
                    : $this->mediaService->upload($store, $data['logo'], 'logo');
            }

            if (!empty($data['cover_images'])) {
                $this->mediaService->deleteByCollection($store, 'cover');
                $this->mediaService->uploadMultiple($store, $data['cover_images'], 'cover');
            }

            return $store;
        });
    }

    /* =========================================================
     | Delete
     ========================================================= */

    public function delete(Store $store): void
    {
        DB::transaction(function () use ($store) {

            $this->mediaService->deleteMany($store->media);

            $store->delete();
        });
    }
}
