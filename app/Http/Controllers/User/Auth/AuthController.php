<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Requests\User\Auth\SendOtpRequest;
use App\Http\Requests\User\Auth\SendPasswordRequest;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Requests\User\Auth\VerifyOtpRequest;
use App\Http\Requests\User\Auth\VerifyPasswordRequest;
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

        return $this->sendResponse(
            message: __('custom.Success')
        );
    }

    /* =========================
        Login
    ========================= */

    public function login(UserLoginRequest $request)
    {
        $user = $this->service->login($request->validated());

        return $this->sendResponse(
            __('custom.Success'),
            data: $user
        );
    }

    /* =========================
        OTP
    ========================= */

    public function sendOtp(SendOtpRequest $request)
    {
        $this->service->sendPasswordOtp($request->validated());

        return $this->sendResponse(
            __('custom.Success')
        );
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = $this->service->verifyOtp($request->validated());

        return $this->sendResponse(
            __('custom.Success'),
            data: $user
        );
    }

    /* =========================
        Password Reset
    ========================= */

    public function sendPassword(SendPasswordRequest $request)
    {
        $this->service->sendPasswordOtp($request->validated());

        return $this->sendResponse(
            __('custom.Success')
        );
    }

    public function verifyPassword(VerifyPasswordRequest $request)
    {
        $user = $this->service->verifyPassword($request->validated());

        return $this->sendResponse(
            __('custom.Success'),
            data: $user
        );
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->service->resetPassword(
            auth('users')->id(),
            $request->validated()['new_password']
        );

        return $this->sendResponse(
            __('custom.Success')
        );
    }

    /* =========================
        Logout
    ========================= */

    public function logout(Request $request)
    {
        $this->service->logout();

        return $this->sendResponse(
            message: __('custom.Success')
        );
    }
}
