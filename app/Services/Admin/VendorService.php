<?php

namespace App\Services\Admin;

use App\Http\Resources\Vendor\AllResource;
use App\Http\Resources\Vendor\AdminOneResource;
use App\Models\Vendor;
use App\Services\BaseService;

class VendorService extends BaseService
{
    private array $unsupportedColumns = [
        'commission_type',
        'fixed_commission',
        'settlement_cycle',
    ];

    public function __construct(Vendor $model)
    {
        $this->model      = $model;
        $this->resource   = AdminOneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['id', 'name', 'owner_name', 'owner_phone'];
        $this->singleImages = [
            'logo'  => 'logo',
        ];
    }

    public function create($data)
    {
        return parent::create($this->sanitizeUnsupportedColumns($data));
    }

    public function update($id, array $data)
    {
        return parent::update($id, $this->sanitizeUnsupportedColumns($data));
    }

    private function sanitizeUnsupportedColumns(array $data): array
    {
        foreach ($this->unsupportedColumns as $column) {
            unset($data[$column]);
        }

        return $data;
    }
}
