<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorFcmToken extends Model
{
    protected $fillable = ['vendor_user_id', 'fcm_token', 'device_name', 'device_type'];
    public $table = 'vendor_fcm_tokens';
    public function vendorUser()
    {
        return $this->belongsTo(VendorUser::class);
    }
}
