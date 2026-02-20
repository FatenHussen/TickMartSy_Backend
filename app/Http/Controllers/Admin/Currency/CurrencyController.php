<?php

namespace App\Http\Controllers\Admin\Currency;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Currency\StoreRequest;
use App\Http\Requests\Admin\Currency\UpdateRequest;
use App\Services\Admin\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends BaseCRUDController
{
    public function __construct(CurrencyService $service)
    {
        $this->service = $service;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }

    // public function toggleStatus($id): JsonResponse
    // {
    //     try {
    //         $currency = $this->service->toggleStatus($id);
    //         return $this->success(
    //             new ($this->service->resource)($currency),
    //             __('custom.currency_status_updated_successfully')
    //         );
    //     } catch (\Exception $e) {
    //         return $this->error($e->getMessage(), 400);
    //     }
    // }
}
