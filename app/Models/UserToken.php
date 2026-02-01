<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable = ['device_id', 'fcm_token'];

    public function tokenable()
    {
        return $this->morphTo();
    }
}
