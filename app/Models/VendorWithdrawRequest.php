<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class VendorWithdrawRequest extends Model
{
    use LogsActivity;

    protected $fillable = [
        'vendor_id',
        'amount',
        'status',
        'payment_method',
        'transfer_reference',
        'note',
        'rejection_reason',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
