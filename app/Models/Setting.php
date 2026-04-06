<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const COLOR_KEYS = [
        'main_color',
        'text_color',
        'second_color',
    ];

    public const DARK_COLOR_KEYS = [
        'dark_main_color',
        'dark_text_color',
        'dark_second_color',
    ];

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
