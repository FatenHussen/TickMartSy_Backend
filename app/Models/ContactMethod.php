<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'type',
        'value',
        'icon',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }
}
