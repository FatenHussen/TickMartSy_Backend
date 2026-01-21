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
        $this->pagination = false;
    }

    public function getAll($filters = [], $config = [])
    {
        /** @var User */
        $user = auth('user')->user();
        // $user = User::findOrFail(2);
        $filters['user_id'] = $user->id;
        return parent::getAll($filters, $config);
    }


    public function create($data)
    {
        /** @var User $user */
        $user = auth('user')->user();
        // $user = User::findOrFail(1);

        // Attach address to authenticated user
        $data['user_id'] = $user->id;

        // If this address is default, unset previous defaults
        if (isset($data['is_default']) && $data['is_default'] === true) {
            $user->addresses()->update(['is_default' => false]);
        }

        return parent::create($data);
    }


    public function update($id, $data)
    {
        /** @var User $user */
        $user = auth('user')->user();
        // $user = User::findOrFail(2);

        $address = $user->addresses()->findOrFail($id);

        // If this address is set as default, unset others
        if (isset($data['is_default']) && $data['is_default'] === true) {
            $user->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return new $this->resource($address);
    }
}
