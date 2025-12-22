<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Store\StoreRequest;
use App\Http\Requests\Admin\Store\UpdateRequest;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\StoreUpdateRequest;
use App\Http\Resources\Admin\Admin\OneResource;
use App\Models\Store;
use App\Services\Admin\StoreService;

class StoreCrudController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index()
    {
        $stores = $this->storeService->list();

        return OneResource::collection($stores);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $store = $this->storeService->create($data);

        return new OneResource($store);
    }

    public function show(Store $store)
    {
        return new OneResource($store);
    }

    public function update(UpdateRequest $request, Store $store)
    {
        $store = $this->storeService->update($store, $request->validated());

        return new OneResource($store);
    }

    public function destroy(Store $store)
    {
        $this->storeService->delete($store);

        return response()->json([
            'message' => 'Store deleted successfully'
        ], 200);
    }
}
