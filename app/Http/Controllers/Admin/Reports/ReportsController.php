<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportsService;
use App\Services\Admin\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportsController extends Controller
{
    public function __construct(
        private ReportsService $reportsService,
        private ExportService $exportService
    ) {}

    /**
     * Get sales report
     */
    public function sales(Request $request): JsonResponse
    {
        $filters = $request->only([
            'from_date',
            'to_date',
            'governorate_id',
            'city_id',
            'vendor_id',
            'shop_id',
            'payment_method',
        ]);

        $data = $this->reportsService->getSalesReport($filters);

        return response()->json([
            'status' => true,
            'message' => 'Sales report retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get product movement report
     */
    public function productMovement(Request $request): JsonResponse
    {
        $filters = $request->only([
            'from_date',
            'to_date',
            'category_id',
        ]);

        $data = $this->reportsService->getProductMovementReport($filters);

        return response()->json([
            'status' => true,
            'message' => 'Product movement report retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get vendor performance report
     */
    public function vendorPerformance(Request $request, int $vendorId): JsonResponse
    {
        $filters = $request->only(['from_date', 'to_date']);

        $data = $this->reportsService->getVendorPerformanceReport($vendorId, $filters);

        return response()->json([
            'status' => true,
            'message' => 'Vendor performance report retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get driver performance report
     */
    public function driverPerformance(Request $request, int $driverId): JsonResponse
    {
        $filters = $request->only(['from_date', 'to_date']);

        $data = $this->reportsService->getDriverPerformanceReport($driverId, $filters);

        return response()->json([
            'status' => true,
            'message' => 'Driver performance report retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get sales by location report
     */
    public function salesByLocation(Request $request): JsonResponse
    {
        $filters = $request->only(['from_date', 'to_date']);

        $data = $this->reportsService->getSalesByLocationReport($filters);

        return response()->json([
            'status' => true,
            'message' => 'Sales by location report retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get sales by category report
     */
    public function salesByCategory(Request $request): JsonResponse
    {
        $filters = $request->only(['from_date', 'to_date']);

        $data = $this->reportsService->getSalesByCategoryReport($filters);

        return response()->json([
            'status' => true,
            'message' => 'Sales by category report retrieved successfully',
            'data' => $data,
        ]);
    }



    /**
     * Export sales report
     */
    public function exportSales(Request $request)
    {
        $filters = $request->only([
            'from_date',
            'to_date',
            'governorate_id',
            'city_id',
            'vendor_id',
            'shop_id',
            'payment_method',
        ]);

        $format = $request->input('format', 'excel'); // excel or pdf

        $data = $this->reportsService->getSalesReport($filters);

        if ($format === 'pdf') {
            return $this->exportService->exportSalesToPdf($data, $filters);
        }

        return $this->exportService->exportSalesToExcel($data, $filters);
    }

    /**
     * Export product movement report
     */
    public function exportProductMovement(Request $request)
    {
        $filters = $request->only(['from_date', 'to_date', 'category_id']);
        $format = $request->input('format', 'excel');

        $data = $this->reportsService->getProductMovementReport($filters);

        if ($format === 'pdf') {
            return $this->exportService->exportProductMovementToPdf($data, $filters);
        }

        return $this->exportService->exportProductMovementToExcel($data, $filters);
    }

    /**
     * Export vendor performance report
     */
    public function exportVendorPerformance(Request $request, int $vendorId)
    {
        $filters = $request->only(['from_date', 'to_date']);
        $format = $request->input('format', 'excel');

        $data = $this->reportsService->getVendorPerformanceReport($vendorId, $filters);

        if ($format === 'pdf') {
            return $this->exportService->exportVendorPerformanceToPdf($data, $filters);
        }

        return $this->exportService->exportVendorPerformanceToExcel($data, $filters);
    }

    /**
     * Export driver performance report
     */
    public function exportDriverPerformance(Request $request, int $driverId)
    {
        $filters = $request->only(['from_date', 'to_date']);
        $format = $request->input('format', 'excel');

        $data = $this->reportsService->getDriverPerformanceReport($driverId, $filters);

        if ($format === 'pdf') {
            return $this->exportService->exportDriverPerformanceToPdf($data, $filters);
        }

        return $this->exportService->exportDriverPerformanceToExcel($data, $filters);
    }
}
