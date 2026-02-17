<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\User\Auth\StoreTokenRequest;
use App\Http\Resources\Admin\OneResource;
use App\Models\Admin;
use App\Models\User;
use App\Services\Admin\AuthService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class AuthController extends Controller
{
    public function __construct(public AuthService $auth_service) {}
    public function login(LoginRequest $request)
    {

        $request_data = $request->validated();

        /** @var Admin */
        $admin = Admin::where('email', $request_data['email'])->first();

        $response = $this->auth_service->login($admin, $request_data);

        return $this->sendResponse(data: $response);
    }

    public function profile()
    {

        /** @var Admin */
        $admin = auth('admin')->user();

        return $this->sendResponse(data: OneResource::make($admin));
    }
    public function logout()
    {
        /** @var Admin */
        $admin = auth('admin')->user();

        $admin->currentAccessToken()?->delete();

        return $this->sendResponse();
    }

    public function storOrUpdateToken(StoreTokenRequest $request)
    {
        /** @var Admin */
        $user = auth('admin')->user();

        $this->auth_service->storeOrUpdateToken($user, $request->all());

        return $this->sendResponse();
    }
}
