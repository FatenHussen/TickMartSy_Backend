<?php
namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\InactiveAccountException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnActivatedException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService{

    public function login($user , $credentials){
        
        if(! $user){
            throw new NotFoundException();
        }

        if(! $user->is_active){
            throw new InactiveAccountException();
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException();
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}