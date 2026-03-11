<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VendorNotification extends Model
{
    use HasUuids;

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

    public function getRecipients()
    {
        return collect([$this->vendorUser]);
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

