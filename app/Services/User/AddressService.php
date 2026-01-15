<?php

namespace App\Services\User;

use App\Http\Resources\Address\OneResource;
use App\Http\Resources\User\City\CityResource;
use App\Models\City;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\BaseService;

class AddressService extends BaseService
{
    public function __construct(UserAddress $model)
    {
        $this->model = $model;
        $this->collection = OneResource::class;
        $this->resource = OneResource::class;
    }

    public function create($data)
    {
        /** @var User */
        $user = auth('user')->user();
        $user = User::find(1);
        $data['user_id'] = $user->id;
        if (!empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }
        return parent::create($data);
    }

    public function update($id, array $data)
    {
        /** @var User */
        $user = auth('user')->user();
        $user = User::find(1);

        if (!empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }
        return parent::update($id, $data);
    }
}
