<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Notification extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $fillable = ['notifiable_type', 'notifiable_id', 'title', 'body', 'is_read', 'is_admin_send','type', 'payload'];
   
    protected $translatable = ['title', 'body'];
    protected $casts = [
        'is_admin_send' => 'boolean',
    ];
    public function notifiable()
    {
        return $this->morphTo();
    }
}