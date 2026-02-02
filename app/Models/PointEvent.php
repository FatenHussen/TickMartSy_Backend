<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_key',
    ];
}
