<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\SendOtpRequest;
use App\Http\Requests\User\SendPasswordRequest;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserRegisterRequest;
use App\Http\Requests\User\VerifyOtpRequest;
use App\Http\Requests\User\VerifyPasswordRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $service;
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
    public function register(UserRegisterRequest $request)
    {
        $data = $request->validated();
        $res = $this->service->register($data);
        return $this->sendResponse(message: __('custom.Success'));
    }

    public function verify_otp(VerifyOtpRequest $request)
    {
        $data = $request->validated();
        $res = $this->service->verify_otp($data);
        return $this->sendResponse(__('custom.Success'), data: $res);
    }

    public function send_otp(SendOtpRequest $request)
    {
        $data = $request->validated();
        $user = User::query();
        if (!empty($data['phone'])) {
            $user->where('phone', $data['phone']);
        }
        if (!empty($data['email'])) {
            $user->where('email', $data['email']);
        }
        $user = $user->first();
       $res = $this->service->send_otp($data, $user->id);
        if ($res == true) {
            return $this->sendResponse(__('custom.Success'), data: $res);
        }
        return $this->sendError(message: __('custom.Error'));
    }

    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();
        $res = $this->service->login($data);
        return $this->sendResponse(__('custom.Success'), data: $res);
    }
    public function send_password(SendPasswordRequest $request)
    {
        $data = $request->validated();
        $user = User::query();

        if (!empty($data['phone'])) {
            $user->where('phone', $data['phone']);
        }

        if (!empty($data['email'])) {
            $user->where('email', $data['email']);
        }

        $user = $user->first();

        Log::info($user);
        $res = $this->service->send_password($data,$user->id);
        if ($res == true) {
            return $this->sendResponse(__('custom.Success'), data: $res);
        }
        return $this->sendError(message: __('custom.Error'));
    }

    public function reset_password(ResetPasswordRequest $request)
    {
        $password = $request->validated();
        $new_password = $password['new_password'];
        $user = auth('users')->id();
        $res = $this->service->reset_password($user, $new_password);
        if ($res == true) {
            return $this->sendResponse(__('custom.Success'), data: $res);
        }
        return $this->sendError(message: __('custom.Error'));
    }
    public function verify_password(VerifyPasswordRequest $request)
    {
        $data = $request->validated();
        $res = $this->service->verify_password($data);
        return $this->sendResponse(__('custom.Success'), data: $res);
    }

    public function logout(Request $request)
    {
        $res = $this->service->logout($request);
        return $this->sendResponse(message: __('custom.Success'));
    }
}