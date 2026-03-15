<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductMovementExport implements WithMultipleSheets
{
    protected $data;
    protected $filters;

    public function __construct(array $data, array $filters = [])
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new TopSellingSheet($this->data['top_selling']),
            new LeastSellingSheet($this->data['least_selling']),
            new InactiveProductsSheet($this->data['inactive_products']),
        ];
    }
}

class TopSellingSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                $item['product_id'],
                $item['name']['ar'] ?? $item['name']['en'] ?? 'N/A',
                $item['total_sold'],
                number_format($item['total_revenue'], 2),
            ];
        });
    }

    public function headings(): array
    {
        return ['Product ID', 'Product Name', 'Total Sold', 'Total Revenue'];
    }

    public function title(): string
    {
        return 'Top Selling';
    }
}

class LeastSellingSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                $item['product_id'],
                $item['name']['ar'] ?? $item['name']['en'] ?? 'N/A',
                $item['total_sold'],
                number_format($item['total_revenue'], 2),
            ];
        });
    }

    public function headings(): array
    {
        return ['Product ID', 'Product Name', 'Total Sold', 'Total Revenue'];
    }

    public function title(): string
    {
        return 'Least Selling';
    }
}

class InactiveProductsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                $item['id'],
                $item['name']['ar'] ?? $item['name']['en'] ?? 'N/A',
                $item['sku'],
                $item['category']['ar'] ?? $item['category']['en'] ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return ['Product ID', 'Product Name', 'SKU', 'Category'];
    }

    public function title(): string
    {
        return 'Inactive Products';
    }
}
