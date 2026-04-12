<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, LogsActivity, SoftDeletes;

    protected $guard_name = 'admin';

    protected $fillable = [
        'phone',
        'name',
        'email',
        'password',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }



    public function fcmTokens(): MorphMany
    {
        return $this->morphMany(UserToken::class, 'tokenable');
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'admin_area');
    }

    public function areaIds(): Collection
    {
        return $this->areas()
            ->pluck('areas.id')
            ->unique()
            ->values();
    }
}
