<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VendorPerformanceExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;
    protected $filters;

    public function __construct(array $data, array $filters = [])
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function collection()
    {
        // Handle both string and array formats for vendor_name
        $vendorName = 'N/A';
        if (isset($this->data['vendor_name'])) {
            $vendorName = is_array($this->data['vendor_name'])
                ? ($this->data['vendor_name']['ar'] ?? $this->data['vendor_name']['en'] ?? 'N/A')
                : $this->data['vendor_name'];
        }

        return collect([
            ['Vendor ID', $this->data['vendor_id'] ?? 'N/A'],
            ['Vendor Name', $vendorName],
            ['Total Sales', number_format($this->data['total_sales'] ?? 0, 2)],
            ['Total Orders', $this->data['total_orders'] ?? 0],
            ['Average Order Value', number_format($this->data['average_order_value'] ?? 0, 2)],
            ['Total Shops', $this->data['total_shops'] ?? 0],
            ['Active Shops', $this->data['active_shops'] ?? 0],
            ['Average Rating', number_format($this->data['average_rating'] ?? 0, 2)],
            ['Total Ratings', $this->data['total_ratings'] ?? 0],
            ['Customer Satisfaction', number_format($this->data['customer_satisfaction'] ?? 0, 2) . '%'],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Vendor Performance';
    }
}
