<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        return $this->hasMany(VendorNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(VendorNotification::class)->whereNull('read_at');
    }

    public function fcmTokens()
    {
        return $this->hasMany(VendorFcmToken::class, 'vendor_user_id');
    }
}
