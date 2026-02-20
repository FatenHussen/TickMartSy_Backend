<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Currency\AllResource;
use App\Http\Resources\Admin\Currency\OneResource;
use App\Models\Currency;
use App\Services\BaseService;

class CurrencyService extends BaseService
{
    public function __construct(Currency $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['code', 'name'];
    }

    public function store(array $data)
    {
        // إذا كانت العملة الجديدة افتراضية، إلغاء الافتراضية من العملات الأخرى
        if ($data['is_default'] ?? false) {
            $this->model->where('is_default', true)->update(['is_default' => false]);
        }

        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $currency = $this->model->findOrFail($id);

        if ($data['is_default'] ?? false) {
            $this->model->where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $currency->update($data);
        return $currency;
    }

    public function toggleStatus($id)
    {
        $currency = $this->model->findOrFail($id);

        // لا يمكن تعطيل العملة الافتراضية
        if ($currency->is_default && $currency->is_active) {
            throw new \Exception(__('custom.cannot_deactivate_default_currency'));
        }

        $currency->update(['is_active' => !$currency->is_active]);
        return $currency;
    }
}
