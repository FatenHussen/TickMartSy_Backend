<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use LogsActivity;
    protected $fillable = [
        'title',
        'body',
        'type',
        'is_fixed',
        'target_page',
        'emoji',
        'media_type',
        'media_url',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
    ];
}
