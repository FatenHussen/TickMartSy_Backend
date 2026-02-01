<?php

// app/Models/PointTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'rule_id',
        'created_by_admin_id',
        'source',
        'points',
        'status',
        'reference_type',
        'reference_id',
        'expires_at',
        'reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(PointWallet::class, 'wallet_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PointRule::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
}

