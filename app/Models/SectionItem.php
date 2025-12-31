<?php

namespace App\Models;

use App\Models\Section;
use Illuminate\Database\Eloquent\Model;

class SectionItem extends Model
{
    protected $fillable = [
        'section_id',
        'item_type',
        'item_id',
        'link',
        'order'
    ];

    public function item()
    {
        return $this->morphTo();
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
