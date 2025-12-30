<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\InactiveAccountException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnActivatedException;
use App\Http\Resources\Admin\OneResource;
use App\Models\Admin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    public function login(Admin $admin, array $credentials)
    {

        if (! $admin) {
            throw new NotFoundException();
        }

        if (! $admin->is_active) {
            throw new InactiveAccountException();
        }

        if (! Hash::check($credentials['password'], $admin->password)) {
            throw new AuthenticationException();
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return [
            'user'  => OneResource::make($admin),
            'token' => $token,
        ];
    }
}
