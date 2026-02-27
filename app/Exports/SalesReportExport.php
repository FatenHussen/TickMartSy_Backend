<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
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
        $rows = collect();

        // Summary row
        $rows->push([
            'Summary',
            '',
            '',
            '',
            '',
        ]);
        $rows->push([
            'Total Orders',
            $this->data['total_orders'],
            '',
            '',
            '',
        ]);
        $rows->push([
            'Total Revenue',
            number_format($this->data['total_revenue'], 2),
            '',
            '',
            '',
        ]);
        $rows->push([
            'Total Delivery Fees',
            number_format($this->data['total_delivery_fees'], 2),
            '',
            '',
            '',
        ]);
        $rows->push([
            'Total Discounts',
            number_format($this->data['total_discounts'], 2),
            '',
            '',
            '',
        ]);
        $rows->push([
            'Average Order Value',
            number_format($this->data['average_order_value'], 2),
            '',
            '',
            '',
        ]);

        // Empty row
        $rows->push(['', '', '', '', '']);

        // Orders details header
        $rows->push([
            'Order Code',
            'Customer',
            'Total',
            'Delivery Fee',
            'Delivered At',
        ]);

        // Orders data
        foreach ($this->data['orders'] as $order) {
            $rows->push([
                $order['order_code'],
                $order['user'],
                number_format($order['total'], 2),
                number_format($order['delivery_price'], 2),
                $order['delivered_at'],
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Sales Report',
            'Generated: ' . now()->format('Y-m-d H:i:s'),
            '',
            '',
            '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Sales Report';
    }
}
