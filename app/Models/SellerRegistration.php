<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerRegistration extends Model
{
    use HasFactory;

    protected $table = 'seller_registrations';

    protected $fillable = [
        'email',
        'password',
        'seller_name',
        'store_name',
        'registered_at',
        'address',
        'commercial_register_number',
        'commercial_register_date',
        'country',
        'city_id',
        'governorate_id',
        'logo',
        'status',
    ];


    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
