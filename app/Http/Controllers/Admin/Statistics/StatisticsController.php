<?php

namespace App\Http\Controllers\Admin\Statistics;

use App\Http\Controllers\Controller;
use App\Services\Admin\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    /**
     * Get dashboard overview statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);

        $data = $this->statisticsService->getDashboardOverview($year);

        return response()->json([
            'status' => true,
            'message' => 'Dashboard statistics retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get entity counts
     */
    public function counts(): JsonResponse
    {
        $data = $this->statisticsService->getCounts();

        return response()->json([
            'status' => true,
            'message' => 'Counts retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get monthly performance
     */
    public function monthlyPerformance(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);

        $data = $this->statisticsService->getMonthlyPerformance($year);

        return response()->json([
            'status' => true,
            'message' => 'Monthly performance retrieved successfully',
            'data' => [
                'year' => $year,
                'monthly_performance' => $data,
            ],
        ]);
    }

    /**
     * Get orders by status
     */
    public function ordersByStatus(): JsonResponse
    {
        $data = $this->statisticsService->getOrdersByStatus();

        return response()->json([
            'status' => true,
            'message' => 'Orders by status retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get top shops
     */
    public function topShops(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $data = $this->statisticsService->getTopShops($limit);

        return response()->json([
            'status' => true,
            'message' => 'Top shops retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get revenue trend (Line Chart)
     */
    public function revenueTrend(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);

        $data = $this->statisticsService->getRevenueTrend($days);

        return response()->json([
            'status' => true,
            'message' => 'Revenue trend retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get orders by hour (Bar Chart)
     */
    public function ordersByHour(): JsonResponse
    {
        $data = $this->statisticsService->getOrdersByHour();

        return response()->json([
            'status' => true,
            'message' => 'Orders by hour retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get orders by day of week (Bar Chart)
     */
    public function ordersByDayOfWeek(): JsonResponse
    {
        $data = $this->statisticsService->getOrdersByDayOfWeek();

        return response()->json([
            'status' => true,
            'message' => 'Orders by day of week retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get revenue by payment method (Pie Chart)
     */
    public function revenueByPaymentMethod(): JsonResponse
    {
        $data = $this->statisticsService->getRevenueByPaymentMethod();

        return response()->json([
            'status' => true,
            'message' => 'Revenue by payment method retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get top categories by revenue (Doughnut Chart)
     */
    public function topCategoriesByRevenue(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 8);

        $data = $this->statisticsService->getTopCategoriesByRevenue($limit);

        return response()->json([
            'status' => true,
            'message' => 'Top categories retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get user growth (Area Chart)
     */
    public function userGrowth(Request $request): JsonResponse
    {
        $months = $request->input('months', 12);

        $data = $this->statisticsService->getUserGrowth($months);

        return response()->json([
            'status' => true,
            'message' => 'User growth retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get order status funnel (Funnel Chart)
     */
    public function orderStatusFunnel(): JsonResponse
    {
        $data = $this->statisticsService->getOrderStatusFunnel();

        return response()->json([
            'status' => true,
            'message' => 'Order status funnel retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get average order value trend (Line Chart)
     */
    public function averageOrderValueTrend(Request $request): JsonResponse
    {
        $months = $request->input('months', 6);

        $data = $this->statisticsService->getAverageOrderValueTrend($months);

        return response()->json([
            'status' => true,
            'message' => 'Average order value trend retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get driver performance comparison (Radar Chart)
     */
    public function driverPerformanceComparison(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);

        $data = $this->statisticsService->getDriverPerformanceComparison($limit);

        return response()->json([
            'status' => true,
            'message' => 'Driver performance comparison retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get product stock levels (Gauge Chart)
     */
    public function productStockLevels(): JsonResponse
    {
        $data = $this->statisticsService->getProductStockLevels();

        return response()->json([
            'status' => true,
            'message' => 'Product stock levels retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get sales heatmap (Heatmap)
     */
    public function salesHeatmap(): JsonResponse
    {
        $data = $this->statisticsService->getSalesHeatmap();

        return response()->json([
            'status' => true,
            'message' => 'Sales heatmap retrieved successfully',
            'data' => $data,
        ]);
    }
}
