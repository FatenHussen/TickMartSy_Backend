<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\ProductMovementExport;
use App\Exports\VendorPerformanceExport;
use App\Exports\DriverPerformanceExport;

class ExportService
{
    /**
     * Export sales report to Excel
     */
    public function exportSalesToExcel(array $data, array $filters = [])
    {
        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new SalesReportExport($data, $filters),
            $filename
        );
    }

    /**
     * Export sales report to PDF
     */
    public function exportSalesToPdf(array $data, array $filters = [])
    {
        $pdf = Pdf::loadView('exports.pdf.sales-report', [
            'data' => $data,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export product movement to Excel
     */
    public function exportProductMovementToExcel(array $data, array $filters = [])
    {
        $filename = 'product_movement_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new ProductMovementExport($data, $filters),
            $filename
        );
    }

    /**
     * Export product movement to PDF
     */
    public function exportProductMovementToPdf(array $data, array $filters = [])
    {
        $pdf = Pdf::loadView('exports.pdf.product-movement', [
            'data' => $data,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'product_movement_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export vendor performance to Excel
     */
    public function exportVendorPerformanceToExcel(array $data, array $filters = [])
    {
        $filename = 'vendor_performance_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new VendorPerformanceExport($data, $filters),
            $filename
        );
    }

    /**
     * Export vendor performance to PDF
     */
    public function exportVendorPerformanceToPdf(array $data, array $filters = [])
    {
        $pdf = Pdf::loadView('exports.pdf.vendor-performance', [
            'data' => $data,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'vendor_performance_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export driver performance to Excel
     */
    public function exportDriverPerformanceToExcel(array $data, array $filters = [])
    {
        $filename = 'driver_performance_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new DriverPerformanceExport($data, $filters),
            $filename
        );
    }

    /**
     * Export driver performance to PDF
     */
    public function exportDriverPerformanceToPdf(array $data, array $filters = [])
    {
        $pdf = Pdf::loadView('exports.pdf.driver-performance', [
            'data' => $data,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'driver_performance_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }
}
