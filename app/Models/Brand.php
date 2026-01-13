<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Brand extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected $fillable = ['name', 'image'];
    public $translatable = ['name'];

    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function toSectionArray(): array
    {
        return [
            'id'       => $this->id,
            'title'     => $this->name,
            'desc'     => null,
            'image'    => $this->image_url,
            'price' => null,
            'price_after_discount' => null,
            'discount' => null,
            'top_badges' => [],
            'bottom_badges' => [],
        ];
    }
}
