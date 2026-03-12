<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;

class VendorUser extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;
    public $table = "vendor_users";
    protected $guard_name = 'vendor-user';
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'vendor_id'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function shops()
    {
        return $this->belongsToMany(
            \App\Models\Shop::class,
            'shop_users',
            'vendor_user_id',
            'shop_id'
        );
    }

    public function notifications()
    {
        return $this->morphMany(VendorNotification::class, 'notifiable')
            ->where('notifiable_id', $this->id)
            ->where('notifiable_type', self::class)
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->morphMany(VendorNotification::class, 'notifiable')
            ->where('notifiable_id', $this->id)
            ->where('notifiable_type', self::class)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function readNotifications()
    {
        return $this->morphMany(VendorNotification::class, 'notifiable')
            ->where('notifiable_id', $this->id)
            ->where('notifiable_type', self::class)
            ->whereNotNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function fcmTokens()
    {
        return $this->hasMany(VendorFcmToken::class, 'vendor_user_id');
    }
    // public function fcmTokens(): MorphMany
    // {
    //     return $this->morphMany(UserToken::class, 'tokenable');
    // }
}
