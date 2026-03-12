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
        'notifiable_type',
        'notifiable_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function vendorUser()
    {
        return $this->belongsTo(VendorUser::class, 'vendor_user_id');
    }

    // Override notifiable relationship for polymorphic
    public function notifiable()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Override to ensure vendor_user_id is set
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (!$notification->vendor_user_id && $notification->notifiable_id) {
                $notification->vendor_user_id = $notification->notifiable_id;
            }
            if (!$notification->notifiable_id && $notification->vendor_user_id) {
                $notification->notifiable_id = $notification->vendor_user_id;
                $notification->notifiable_type = VendorUser::class;
            }
        });
    }
}

