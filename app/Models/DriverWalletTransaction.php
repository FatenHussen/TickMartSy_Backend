<?php

namespace App\Models;

use App\Models\Concerns\AppliesAreaScope;
use Illuminate\Database\Eloquent\Model;

class DriverWalletTransaction extends Model
{
    use AppliesAreaScope;

    protected static array $areaRelationPaths = ['order.address'];

    protected $fillable = [
        'driver_id',
        'type',
        'amount',
        'delivery_fee',
        'rate_percent',
        'order_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
