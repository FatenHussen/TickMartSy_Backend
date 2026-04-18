<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateUserVisit extends Model
{
    protected $fillable = [
        'affiliate_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
