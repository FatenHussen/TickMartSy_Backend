<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'area_id',
        'image',
        'preferred_payment_method_id',

        //markter
        'is_affiliate',
        'affiliate_approved',
        'affiliate_id',
        'coupon_id',
        'affiliate_rate',
        'currency_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mainImage',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'is_affiliate' => 'boolean',
        'affiliate_approved' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    // public function images()
    // {
    //     return $this->morphMany(Image::class, 'imageable');
    // }

    // protected $appends = ['image_path'];

    // public function mainImage()
    // {
    //     return $this->morphOne(Image::class, 'imageable')->where('is_main', true);
    // }

    // public function getImagePathAttribute()
    // {
    //     return $this->mainImage?->path;
    // }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class, 'user_id');
    // }

    public function pointWallet()
    {
        return $this->hasOne(PointWallet::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function fcmTokens()
    {
        return $this->morphMany(UserToken::class, 'tokenable');
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function userBasketSchedules()
    {
        return $this->hasMany(UserBasketSchedule::class);
    }

    public function preferredPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'preferred_payment_method_id');
    }
}
