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
            ['Driver ID', $this->data['driver_id']],
            ['Driver Name', $this->data['driver_name']],
            ['Total Orders', $this->data['total_orders']],
            ['Total Earnings', number_format($this->data['total_earnings'], 2)],
            ['Average Delivery Time (minutes)', number_format($this->data['average_delivery_time_minutes'], 2)],
            ['Average Rating', number_format($this->data['average_rating'], 2)],
            ['Total Ratings', $this->data['total_ratings']],
            ['Total Complaints', $this->data['total_complaints']],
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
