<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Requests\User\Auth\SendOtpRequest;
use App\Http\Requests\User\Auth\SendPasswordRequest;
use App\Http\Requests\User\Auth\StoreTokenRequest;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Requests\User\Auth\VerifyOtpRequest;
use App\Http\Requests\User\Auth\VerifyPasswordRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private UserService $service) {}

    /* =========================
        Register
    ========================= */

    public function register(UserRegisterRequest $request)
    {
        $this->service->register($request->validated());

        return $this->sendResponse();
    }

    /* =========================
        Login
    ========================= */


    public function login(UserLoginRequest $request)
    {
        $user = $this->service->login($request->validated());

        return $this->sendResponse(
            data: $user
        );
    }

    /* =========================
        OTP
    ========================= */

    public function sendOtp(SendOtpRequest $request)
    {
        $this->service->sendPasswordOtp($request->validated());

        return $this->sendResponse();
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = $this->service->verifyOtp($request->validated());

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
            auth('user')->id(),
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
        /** @var User */
        $user = auth('user')->user();

        $this->service->storeOrUpdateToken($user, $request->all());

        return $this->sendResponse();
    }

    public function markterRequest(Request $request)
    {
        /** @var User */
        $user = auth('user')->user();

        $this->service->markterRequest($user, $request->all());

        return $this->sendResponse(message: __('custom.marketer.wait_for_admin_response'));
    }
}
