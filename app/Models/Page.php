<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug'];

    public function pageSections()
    {
        return $this->hasMany(PageSection::class)->orderBy('order');
    }
}
