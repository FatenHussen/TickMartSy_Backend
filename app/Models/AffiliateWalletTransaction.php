<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateWalletTransaction extends Model
{
    protected $fillable = [
        'affiliate_id',
        'type',           // commission / withdraw
        'amount',         // موجب للعمولة، سالب للسحب
        'order_id',
    ];

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id', 'affiliate_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
