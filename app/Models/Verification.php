<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'type',
        'value',
        'verified_at',
        'end_at',
        'driver_id',

    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'end_at'      => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->end_at && $this->end_at->isPast();
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    
    public function isVerification(): bool
    {
        return $this->type === 'verification';
    }

    public function isResetPassword(): bool
    {
        return $this->type === 'reset_password';
    }

    public function isUpdateEmail(): bool
    {
        return $this->type === 'update_email';
    }

    public function isUpdatePhone(): bool
    {
        return $this->type === 'update_phone';
    }
}