<?php

namespace App\Models;

use App\Models\Concerns\AppliesAreaScope;
use Illuminate\Database\Eloquent\Model;

class AffiliateWalletTransaction extends Model
{
    use AppliesAreaScope;
    protected static array $areaRelationPaths = ['order.address'];

    protected $fillable = [
        'affiliate_id',
        'type',
        'amount',
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
