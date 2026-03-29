<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'is_active',
    ];

    protected $casts = [
        'value' => 'array', // حتى يدعم json تلقائياً
    ];
}
