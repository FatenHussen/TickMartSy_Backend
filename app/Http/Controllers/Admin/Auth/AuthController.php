<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\User;
use App\Services\Admin\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(public AuthService $auth_service)
    {
    }
    public function login(LoginRequest $request){

        $data=$request->validated();
        $user=User::where('email',$data['email'])->first();
        $this->auth_service->login($data,$user);

    }
}
