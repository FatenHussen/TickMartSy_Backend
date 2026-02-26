<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopUser extends Model
{
    protected $table = 'shop_users';

    protected $fillable = [
        'shop_id',
        'vendor_user_id',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function vendorUser()
    {
        return $this->belongsTo(VendorUser::class);
    }
}
