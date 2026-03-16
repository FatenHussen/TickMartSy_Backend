<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateProfileRequest;
use App\Http\Requests\Driver\UpdatePhoneRequest;
use App\Http\Requests\Driver\VerifyUpdateRequest;
use App\Services\Driver\DriverService;

class ProfileController extends Controller
{
    public function __construct(protected DriverService $service) {}

    public function getProfile()
    {
        $res = $this->service->getProfile();
        return $this->sendResponse(data: $res);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $data = $request->validated();
        $id = auth('driver')->id();
        $result = $this->service->updateProfile($data, $id);
        return $this->sendResponse(data: $result);
    }

    public function updatePhone(UpdatePhoneRequest $request)
    {
        $res = $this->service->updatePhone($request->validated());
        return $this->sendResponse(message: __('custom.driver.otp_sent_new_phone'));
    }

    public function verifyUpdate(VerifyUpdateRequest $request)
    {
        $res = $this->service->verifyUpdate($request->validated());
        return $this->sendResponse(data: $res);
    }
}
