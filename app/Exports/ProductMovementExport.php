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
            // Handle both string and array formats for name
            $name = 'N/A';
            if (isset($item['product_name'])) {
                $name = is_array($item['product_name'])
                    ? ($item['product_name']['ar'] ?? $item['product_name']['en'] ?? 'N/A')
                    : $item['product_name'];
            }

            return [
                $item['product_id'] ?? 'N/A',
                $name,
                $item['total_sold'] ?? 0,
                number_format($item['total_revenue'] ?? 0, 2),
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
            // Handle both string and array formats for name
            $name = 'N/A';
            if (isset($item['product_name'])) {
                $name = is_array($item['product_name'])
                    ? ($item['product_name']['ar'] ?? $item['product_name']['en'] ?? 'N/A')
                    : $item['product_name'];
            }

            return [
                $item['product_id'] ?? 'N/A',
                $name,
                $item['total_sold'] ?? 0,
                number_format($item['total_revenue'] ?? 0, 2),
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
            // Handle both string and array formats for name and category
            $name = 'N/A';
            if (isset($item['name'])) {
                $name = is_array($item['name'])
                    ? ($item['name']['ar'] ?? $item['name']['en'] ?? 'N/A')
                    : $item['name'];
            }

            $category = 'N/A';
            if (isset($item['category'])) {
                $category = is_array($item['category'])
                    ? ($item['category']['ar'] ?? $item['category']['en'] ?? 'N/A')
                    : $item['category'];
            }

            return [
                $item['id'] ?? 'N/A',
                $name,
                $item['sku'] ?? 'N/A',
                $category,
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
