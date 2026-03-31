<?php

namespace App\Services\User;

use App\Http\Resources\User\SellerRegistrationResource;
use App\Models\SellerRegistration;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;

class SellerRegistrationService extends BaseService
{
    protected $singleImages = [
        'logo',
        'commercial_register_image',
    ];
    protected $relations = ['country', 'city', 'governorate'];
    protected $model = SellerRegistration::class;
    protected $resource = SellerRegistrationResource::class;
}
