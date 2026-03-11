<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\DatabaseNotification;

class VendorNotification extends DatabaseNotification
{
    use HasUuids;

    protected $table = 'vendor_notifications';

    protected $fillable = [
        'vendor_user_id',
        'title',
        'body',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function vendorUser()
    {
        return $this->belongsTo(VendorUser::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}

