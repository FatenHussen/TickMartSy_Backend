<?php

namespace App\Services\User;

use App\Http\Resources\Currency\AllResource;
use App\Http\Resources\Currency\OneResource;
use App\Models\Currency;
use App\Services\BaseService;

class CurrencyService extends BaseService
{
    public function __construct(Currency $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = false;
    }

    public function getAllActive()
    {
        return $this->model->active()->get();
    }

    public function getDefault()
    {
        return $this->model->default()->first() ?? $this->model->where('code', 'USD')->first();
    }

    public function getUserCurrency($user)
    {
        if ($user && $user->currency_id) {
            return $this->model->find($user->currency_id);
        }
        return $this->getDefault();
    }

    public function updateUserCurrency($user, $currencyId)
    {
        $currency = $this->model->findOrFail($currencyId);
        $user->update(['currency_id' => $currencyId]);
        $user->save();
        return $currency;
    }
}
