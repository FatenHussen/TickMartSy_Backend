<?php

namespace App\Models;

use App\Models\Concerns\AppliesAreaScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class VendorUser extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes, AppliesAreaScope;
    protected static array $areaRelationPaths = ['shops'];
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
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->morphMany(VendorNotification::class, 'notifiable')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function readNotifications()
    {
        return $this->morphMany(VendorNotification::class, 'notifiable')
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
