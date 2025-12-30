<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['name', 'type', 'api_source', 'filters', 'reusable'];
    protected $casts = ['filters' => 'array'];

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_sections');
    }
}
