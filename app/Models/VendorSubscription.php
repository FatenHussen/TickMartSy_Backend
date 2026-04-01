<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSubscription extends Model
{
    protected $fillable = [
        'vendor_id',
        'vendor_package_id',
        'starts_at',
        'ends_at',
        'auto_renew',
        'status',
        'notified_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'auto_renew' => 'boolean',
        'notified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function package()
    {
        return $this->belongsTo(VendorPackage::class, 'vendor_package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('ends_at', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->isActive() && $this->ends_at->lte(now()->addDays($days));
    }
}
