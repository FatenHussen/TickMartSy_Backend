<?php

namespace App\Imports;

use App\Services\Admin\ProductImportService;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

/**
 * Parses SPBS product template rows. Heading detection is manual (Arabic headers)
 * so we do not use WithHeadingRow slugification.
 */
class ProductsImport implements OnEachRow
{
    /** @var array<int, string> colIndex => header name */
    protected array $columnMap = [];

    protected bool $headersFound = false;

    /** @var list<array{row:int,data:array<string,mixed>}> */
    protected array $mapped = [];

    public function onRow(Row $row): void
    {
        $cells = $row->toArray();
        $excelRow = $row->getIndex();

        if (!$this->headersFound) {
            $known = [];
            foreach ($cells as $colIndex => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $label = trim((string) $value);
                if ($label !== '' && in_array($label, ProductImportService::HEADERS, true)) {
                    $known[$colIndex] = $label;
                }
            }

            if (count($known) >= 3) {
                $this->columnMap = $known;
                $this->headersFound = true;
            }

            return;
        }

        $data = [];
        foreach (ProductImportService::HEADERS as $header) {
            $data[$header] = null;
        }

        foreach ($this->columnMap as $colIndex => $header) {
            $data[$header] = $cells[$colIndex] ?? null;
        }

        $this->mapped[] = [
            'row' => $excelRow,
            'data' => $data,
        ];
    }

    public function headersWereFound(): bool
    {
        return $this->headersFound;
    }

    /**
     * @return list<array{row:int,data:array<string,mixed>}>
     */
    public function mappedRows(): array
    {
        return $this->mapped;
    }
}
