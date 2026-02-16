<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class VendorUser extends Authenticatable
{
    use HasApiTokens, HasRoles;
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
}
