<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointWallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'expire_at',
        'last_earned_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'last_earned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'wallet_id');
    }
}

