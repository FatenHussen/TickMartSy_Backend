<?php

namespace App\Services\Admin;

use App\Helpers\CurrencyHelper;
use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProductImportService
{
    public const HEADERS = [
        'كود المنتج',
        'الفئة الرئيسية',
        'اسم المنتج عربي',
        'اسم المنتج انكليزي',
        'الوصف المختصر',
        'العلامة التجارية',
        'الباركود',
        'السعر دولار',
        'السعر سوري',
        'نوع الخصم',
        'الخصم',
        'الكمية',
        'تاريخ انتهاء الصلاحية',
    ];

    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * @return array{created:int,updated:int,failed:list<array{row:int,errors:list<string>}>}
     */
    public function import(UploadedFile $file): array
    {
        $import = new ProductsImport();
        Excel::import($import, $file);

        if (!$import->headersWereFound()) {
            throw new \InvalidArgumentException(
                'ملف الإكسل لا يحتوي على أعمدة القالب المعتمدة (مثل: كود المنتج، اسم المنتج عربي، الباركود…)'
            );
        }

        $result = [
            'created' => 0,
            'updated' => 0,
            'failed' => [],
        ];

        foreach ($import->mappedRows() as $item) {
            $rowNumber = $item['row'];
            $row = $item['data'];

            try {
                $outcome = $this->processRow($row);
                if ($outcome === 'created') {
                    $result['created']++;
                } elseif ($outcome === 'updated') {
                    $result['updated']++;
                }
                // skipped empty rows return null
            } catch (ProductImportRowException $e) {
                $result['failed'][] = [
                    'row' => $rowNumber,
                    'errors' => $e->errors(),
                ];
            } catch (\Throwable $e) {
                $result['failed'][] = [
                    'row' => $rowNumber,
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return 'created'|'updated'|null
     */
    protected function processRow(array $row): ?string
    {
        if ($this->isEmptyRow($row)) {
            return null;
        }

        $sku = $this->stringOrNull($row['كود المنتج'] ?? null);
        $barcode = $this->stringOrNull($row['الباركود'] ?? null);

        $product = $this->findExisting($barcode, $sku);
        $isCreate = $product === null;

        $errors = $this->validateRow($row, $isCreate, $product);
        if ($errors !== []) {
            throw new ProductImportRowException($errors);
        }

        $payload = $this->buildPayload($row, $isCreate);

        return DB::transaction(function () use ($product, $payload, $isCreate) {
            if ($isCreate) {
                $payload['sale_channel'] = 'platform';
                $resource = $this->productService->create($payload);
                $created = $resource->resource;
                if ($created instanceof Product) {
                    $this->syncDefaultVariant($created, $payload);
                }

                return 'created';
            }

            $this->productService->update($product->id, $payload);
            $product->refresh();
            $this->syncDefaultVariant($product, $payload);

            return 'updated';
        });
    }

    protected function findExisting(?string $barcode, ?string $sku): ?Product
    {
        if ($barcode !== null) {
            $byBarcode = Product::query()->where('barcode', $barcode)->first();
            if ($byBarcode) {
                return $byBarcode;
            }
        }

        if ($sku !== null) {
            return Product::query()->where('sku', $sku)->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    protected function validateRow(array $row, bool $isCreate, ?Product $existing): array
    {
        $errors = [];

        $nameAr = $this->stringOrNull($row['اسم المنتج عربي'] ?? null);
        $nameEn = $this->stringOrNull($row['اسم المنتج انكليزي'] ?? null);
        $categoryName = $this->stringOrNull($row['الفئة الرئيسية'] ?? null);
        $brandName = $this->stringOrNull($row['العلامة التجارية'] ?? null);
        $sku = $this->stringOrNull($row['كود المنتج'] ?? null);
        $priceUsd = $row['السعر دولار'] ?? null;
        $priceSyp = $row['السعر سوري'] ?? null;
        $qty = $row['الكمية'] ?? null;
        $discount = $row['الخصم'] ?? null;
        $discountTypeRaw = $this->stringOrNull($row['نوع الخصم'] ?? null);
        $expiryRaw = $row['تاريخ انتهاء الصلاحية'] ?? null;

        if ($isCreate) {
            if ($nameAr === null) {
                $errors[] = 'اسم المنتج عربي مطلوب';
            }
            if ($nameEn === null) {
                $errors[] = 'اسم المنتج انكليزي مطلوب';
            }
            if ($categoryName === null) {
                $errors[] = 'الفئة الرئيسية مطلوبة';
            }
            if ($this->isBlank($priceUsd) && $this->isBlank($priceSyp)) {
                $errors[] = 'يجب إدخال السعر دولار أو السعر سوري';
            }
            if ($this->isBlank($qty)) {
                $errors[] = 'الكمية مطلوبة';
            }
        }

        if ($categoryName !== null) {
            try {
                $this->resolveCategoryId($categoryName);
            } catch (ProductImportRowException $e) {
                $errors = array_merge($errors, $e->errors());
            }
        }

        if ($brandName !== null) {
            try {
                $this->resolveBrandId($brandName);
            } catch (ProductImportRowException $e) {
                $errors = array_merge($errors, $e->errors());
            }
        }

        if (!$this->isBlank($priceUsd) && !$this->isNonNegativeNumber($priceUsd)) {
            $errors[] = 'السعر دولار يجب أن يكون رقماً أكبر من أو يساوي صفر';
        }
        if (!$this->isBlank($priceSyp) && !$this->isNonNegativeNumber($priceSyp)) {
            $errors[] = 'السعر سوري يجب أن يكون رقماً أكبر من أو يساوي صفر';
        }
        if (!$this->isBlank($qty) && !$this->isNonNegativeInteger($qty)) {
            $errors[] = 'الكمية يجب أن تكون عدداً صحيحاً أكبر من أو يساوي صفر';
        }
        if (!$this->isBlank($discount) && !$this->isNonNegativeInteger($discount)) {
            $errors[] = 'الخصم يجب أن يكون عدداً صحيحاً أكبر من أو يساوي صفر';
        } elseif (!$this->isBlank($discount) && (int) $discount > 100 && $discountTypeRaw !== null) {
            $normalizedType = $this->normalizeDiscountType($discountTypeRaw);
            if ($normalizedType === 'percentage') {
                $errors[] = 'الخصم النسبي يجب ألا يتجاوز 100';
            }
        }

        if ($discountTypeRaw !== null && $this->normalizeDiscountType($discountTypeRaw) === null) {
            $errors[] = 'نوع الخصم غير صالح (none / percentage / fixed أو بدون / نسبة / ثابت)';
        }

        if (!$this->isBlank($expiryRaw) && $this->parseDate($expiryRaw) === null) {
            $errors[] = 'تاريخ انتهاء الصلاحية غير صالح';
        }

        if ($sku !== null) {
            $skuQuery = Product::query()->where('sku', $sku);
            if ($existing) {
                $skuQuery->where('id', '!=', $existing->id);
            }
            if ($skuQuery->exists()) {
                $errors[] = "كود المنتج مستخدم مسبقاً: {$sku}";
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function buildPayload(array $row, bool $isCreate): array
    {
        $payload = [];

        $nameAr = $this->stringOrNull($row['اسم المنتج عربي'] ?? null);
        $nameEn = $this->stringOrNull($row['اسم المنتج انكليزي'] ?? null);
        if ($nameAr !== null || $nameEn !== null) {
            $name = [];
            if ($nameAr !== null) {
                $name['ar'] = $nameAr;
            }
            if ($nameEn !== null) {
                $name['en'] = $nameEn;
            }
            $payload['name'] = $name;
        }

        $description = $this->stringOrNull($row['الوصف المختصر'] ?? null);
        if ($description !== null) {
            $payload['description'] = ['ar' => $description];
        }

        $categoryName = $this->stringOrNull($row['الفئة الرئيسية'] ?? null);
        if ($categoryName !== null) {
            $payload['category_id'] = $this->resolveCategoryId($categoryName);
        }

        $brandName = $this->stringOrNull($row['العلامة التجارية'] ?? null);
        if ($brandName !== null) {
            $payload['brand_id'] = $this->resolveBrandId($brandName);
        }

        $sku = $this->stringOrNull($row['كود المنتج'] ?? null);
        if ($sku !== null) {
            $payload['sku'] = $sku;
        }

        $barcode = $this->stringOrNull($row['الباركود'] ?? null);
        if ($barcode !== null) {
            $payload['barcode'] = $barcode;
        }

        $priceData = [];
        if (!$this->isBlank($row['السعر دولار'] ?? null)) {
            $priceData['price'] = (float) $row['السعر دولار'];
        }
        if (!$this->isBlank($row['السعر سوري'] ?? null)) {
            $priceData['price_syp'] = (float) $row['السعر سوري'];
        }
        if ($priceData !== []) {
            $priceData = CurrencyHelper::applySypPriceInputs($priceData);
            if (array_key_exists('price', $priceData)) {
                $payload['price'] = $priceData['price'];
            }
        }

        if (!$this->isBlank($row['الكمية'] ?? null)) {
            $payload['quantity'] = (int) $row['الكمية'];
        }

        $discountTypeRaw = $this->stringOrNull($row['نوع الخصم'] ?? null);
        if ($discountTypeRaw !== null) {
            $payload['discount_type'] = $this->normalizeDiscountType($discountTypeRaw);
        }

        if (!$this->isBlank($row['الخصم'] ?? null)) {
            $payload['discount'] = (int) $row['الخصم'];
        }

        if (!$this->isBlank($row['تاريخ انتهاء الصلاحية'] ?? null)) {
            $payload['expiry_date'] = $this->parseDate($row['تاريخ انتهاء الصلاحية']);
        }

        if ($isCreate && !isset($payload['discount_type'])) {
            $payload['discount_type'] = 'none';
        }

        return $payload;
    }

    protected function resolveCategoryId(string $name): int
    {
        $matches = Category::query()
            ->where(function ($q) use ($name) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) = ?", [$name])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$name]);
            })
            ->get(['id', 'name']);

        if ($matches->isEmpty()) {
            throw new ProductImportRowException(["الفئة الرئيسية غير موجودة: {$name}"]);
        }
        if ($matches->count() > 1) {
            throw new ProductImportRowException(["الفئة الرئيسية مكررة بالاسم: {$name}"]);
        }

        return (int) $matches->first()->id;
    }

    protected function resolveBrandId(string $name): int
    {
        $matches = Brand::query()
            ->where(function ($q) use ($name) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) = ?", [$name])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$name]);
            })
            ->get(['id', 'name']);

        if ($matches->isEmpty()) {
            throw new ProductImportRowException(["العلامة التجارية غير موجودة: {$name}"]);
        }
        if ($matches->count() > 1) {
            throw new ProductImportRowException(["العلامة التجارية مكررة بالاسم: {$name}"]);
        }

        return (int) $matches->first()->id;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function syncDefaultVariant(Product $product, array $payload): void
    {
        $variant = $product->variants()->orderBy('id')->first();
        if (!$variant) {
            return;
        }

        $update = [];
        foreach (['sku', 'barcode', 'price', 'quantity'] as $key) {
            if (array_key_exists($key, $payload)) {
                $update[$key] = $payload[$key];
            }
        }

        if ($update !== []) {
            $variant->update($update);
        }
    }

    protected function normalizeDiscountType(string $value): ?string
    {
        $normalized = mb_strtolower(trim($value));
        $map = [
            'none' => 'none',
            'بدون' => 'none',
            'لا' => 'none',
            'percentage' => 'percentage',
            'نسبة' => 'percentage',
            'نسبة مئوية' => 'percentage',
            'fixed' => 'fixed',
            'ثابت' => 'fixed',
            'مبلغ ثابت' => 'fixed',
        ];

        return $map[$normalized] ?? null;
    }

    protected function parseDate(mixed $value): ?string
    {
        if ($this->isBlank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach (self::HEADERS as $header) {
            if (!$this->isBlank($row[$header] ?? null)) {
                return false;
            }
        }

        return true;
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($this->isBlank($value)) {
            return null;
        }

        return trim((string) $value);
    }

    protected function isBlank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '') || $value === '';
    }

    protected function isNonNegativeNumber(mixed $value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return is_numeric($value) && (float) $value >= 0;
    }

    protected function isNonNegativeInteger(mixed $value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (!is_numeric($value)) {
            return false;
        }

        return (float) $value >= 0 && (float) $value == (int) $value;
    }
}
