<?php

namespace App\Models;

use App\Models\Concerns\AppliesAreaScope;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawRequest extends Model
{
    use LogsActivity, AppliesAreaScope;
    protected static array $areaRelationPaths = ['affiliate'];
    protected $fillable = [
        'affiliate_id',
        'amount',
        'status', // pending / approved / rejected
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id', 'affiliate_id');
    }
}
