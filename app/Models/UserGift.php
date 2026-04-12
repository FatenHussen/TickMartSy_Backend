<?php

namespace App\Models;

use App\Models\Concerns\AppliesAreaScope;
use Illuminate\Database\Eloquent\Model;

class UserGift extends Model
{
    use AppliesAreaScope;
    protected static array $areaRelationPaths = ['address'];
    protected $fillable = [
        'gift_id',
        'user_id',
        'address_id',
        'status',
        'admin_notes',
        'user_notes',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}
