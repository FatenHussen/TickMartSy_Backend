<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionItem extends Model
{
    protected $fillable = ['section_id', 'item_type', 'item_id', 'link', 'order'];
}
