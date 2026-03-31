<?php

namespace App\Services\User;

use App\Http\Resources\User\PaymentMethod\AllResource;
use App\Models\PaymentMethod;
use App\Services\BaseService;

class PaymentMethodService extends BaseService
{
    public function __construct(PaymentMethod $model)
    {
        $this->model      = $model;
        $this->collection = AllResource::class;
        $this->relations  = [];
        $this->pagination = true;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);
        $query->reorder();

        $defaultId = PaymentMethod::resolvedDefaultId();

        $query->active();

        if ($defaultId) {
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$defaultId]);
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
