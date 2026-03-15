<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DriverPerformanceExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
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
            ['Driver ID', $this->data['driver_id'] ?? 'N/A'],
            ['Driver Name', $this->data['driver_name'] ?? 'N/A'],
            ['Total Orders', $this->data['total_orders'] ?? 0],
            ['Total Earnings', number_format($this->data['total_earnings'] ?? 0, 2)],
            ['Average Delivery Time (minutes)', number_format($this->data['average_delivery_time_minutes'] ?? 0, 2)],
            ['Average Rating', number_format($this->data['average_rating'] ?? 0, 2)],
            ['Total Ratings', $this->data['total_ratings'] ?? 0],
            ['Total Complaints', $this->data['total_complaints'] ?? 0],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Driver Performance';
    }
}
