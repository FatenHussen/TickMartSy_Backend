<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlashSaleRequest;
use App\Models\FlashSale;
use App\Services\Admin\FlashSaleService;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function __construct(private FlashSaleService $service)
    {
    }

    public function index(Request $request)
    {
        $config = [
            'page' => (int) $request->input('page', 1),
            'per_page' => $this->resolvePerPage($request),
        ];

        return $this->sendResponse(data: $this->service->getAll([], $config));
    }

    public function store(FlashSaleRequest $request)
    {
        $flashSale = $this->service->createFlashSale($request->validated());
        return $this->sendResponse(data: $flashSale);
    }

    public function show(FlashSale $flashSale)
    {
        return $this->sendResponse(data: $this->service->getOne($flashSale->id));
    }

    public function update(FlashSaleRequest $request, FlashSale $flashSale)
    {
        $updated = $this->service->updateFlashSale($flashSale, $request->validated());
        return $this->sendResponse(data: $updated);
    }
}
