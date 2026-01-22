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
        'commercial_register_image',
        'gender',
        'country',
        'logo',
        'status',
    ];


    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];
}
