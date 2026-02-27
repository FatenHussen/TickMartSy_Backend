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
        return collect([
            ['Vendor ID', $this->data['vendor_id']],
            ['Vendor Name', $this->data['vendor_name']['ar'] ?? $this->data['vendor_name']['en'] ?? 'N/A'],
            ['Total Sales', number_format($this->data['total_sales'], 2)],
            ['Total Orders', $this->data['total_orders']],
            ['Average Order Value', number_format($this->data['average_order_value'], 2)],
            ['Total Shops', $this->data['total_shops']],
            ['Active Shops', $this->data['active_shops']],
            ['Average Rating', number_format($this->data['average_rating'], 2)],
            ['Total Ratings', $this->data['total_ratings']],
            ['Customer Satisfaction', number_format($this->data['customer_satisfaction'], 2) . '%'],
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
