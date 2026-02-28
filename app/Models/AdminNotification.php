<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use LogsActivity;
    protected $fillable = ['title', 'body', 'type'];
}
