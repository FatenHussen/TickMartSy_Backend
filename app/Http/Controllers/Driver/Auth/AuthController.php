<?php

namespace App\Http\Controllers\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\ResetPasswordRequest;
use App\Http\Requests\Driver\Auth\SendPasswordRequest;
use App\Http\Requests\Driver\Auth\DriverLoginRequest;
use App\Http\Requests\Driver\Auth\VerifyPasswordRequest;
use App\Http\Requests\User\Auth\StoreTokenRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Driver;
use App\Services\Driver\DriverService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private DriverService $service) {}

    /* =========================
        Login
    ========================= */

    public function login(DriverLoginRequest $request)
    {
        $user = $this->service->login($request->validated());

        return $this->sendResponse(
            data: $user
        );
    }

    /* =========================
        Password Reset
    ========================= */

    public function sendPassword(SendPasswordRequest $request)
    {
        $this->service->sendPasswordOtp($request->validated());

        return $this->sendResponse();
    }

    public function verifyPassword(VerifyPasswordRequest $request)
    {
        $user = $this->service->verifyPassword($request->validated());

        return $this->sendResponse(
            data: $user
        );
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = $this->service->resetPassword(
            auth('driver')->id(),
            $request->validated()['new_password']
        );

        return $this->sendResponse(data: $user);
    }

    /* =========================
        Logout
    ========================= */

    public function logout(Request $request)
    {
        $this->service->logout();

        return $this->sendResponse();
    }

    public function storOrUpdateToken(StoreTokenRequest $request)
    {

        /** @var Driver */
        $user = auth('driver')->user();

        $this->service->storeOrUpdateToken($user, $request->all());

        return $this->sendResponse();
    }

    public function notifications()
    {

        /** @var Driver */
        $driver = auth('driver')->user();

        return $this->sendResponse(message: __('custom.Success'), data: NotificationResource::collection($driver->notifications));
    }
}
