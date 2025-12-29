<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class VendorUser extends Authenticatable
{
    use HasApiTokens, HasRoles;

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
