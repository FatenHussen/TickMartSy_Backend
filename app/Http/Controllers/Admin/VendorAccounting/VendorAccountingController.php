<?php

namespace App\Http\Controllers\Admin\VendorAccounting;

use App\Http\Controllers\Controller;
use App\Services\Admin\VendorAccountingService;
use Illuminate\Http\Request;

class VendorAccountingController extends Controller
{
    public function __construct(private VendorAccountingService $service) {}

    public function summary(Request $request)
    {
        $filters = $this->extractFilters($request);

        return $this->sendResponse(data: $this->service->getSummary($filters));
    }

    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);
        $perPage = $this->resolvePerPage($request);

        return $this->sendResponse(data: $this->service->getVendorsAccounting($filters, $perPage));
    }

    public function show(Request $request, int $vendorId)
    {
        $filters = $this->extractFilters($request);
        $withdrawPerPage = $this->resolvePerPage($request, 10, 'withdraw_per_page');

        return $this->sendResponse(data: $this->service->getVendorStatement($vendorId, $filters, $withdrawPerPage));
    }

    private function extractFilters(Request $request): array
    {
        $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'search' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'settlement_cycle' => ['nullable', 'in:weekly,monthly'],
            'withdraw_status' => ['nullable', 'in:pending,paid,rejected'],
        ]);

        return [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'search' => $request->input('search'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
            'settlement_cycle' => $request->input('settlement_cycle'),
            'withdraw_status' => $request->input('withdraw_status'),
        ];
    }
}
