<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawRequest extends Model
{
    use LogsActivity;
    protected $fillable = [
        'affiliate_id',
        'amount',
        'status', // pending / approved / rejected
        'note',
    ];

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id', 'affiliate_id');
    }
}
