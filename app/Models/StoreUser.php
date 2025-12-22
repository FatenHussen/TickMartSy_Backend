<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StoreUser extends Authenticatable
{
    use HasApiTokens , HasRoles;

       protected $fillable = [
        'name',
        'email',
        'password',
        'is_active'
    ];

}
