<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// App/Models/Complaint.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;

class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'message',
        'type',
        'status',
        'admin_response',
        'images'
    ];

    protected $casts = [
        'status' => ComplaintStatus::class,
        'type'   => ComplaintType::class,
        'images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function getImagesAttribute($value): array
    {
        $images = json_decode($value, true) ?? [];

        return array_map(fn($img) => asset('storage/' . $img), $images);
    }
}
