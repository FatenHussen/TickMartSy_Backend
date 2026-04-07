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
        'target_page',
        'emoji',
        'media_type',
        'media_url',
        'channels',
    ];

    protected $casts = [
        'channels' => 'array',
    ];
}
