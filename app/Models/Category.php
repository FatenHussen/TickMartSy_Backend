<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements Sectionable
{
    use HasFactory, HasTranslations, SoftDeletes;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'description',
        'icon',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    // recursively
    public function descendants()
    {
        return $this->children()->with('descendants');
    }
    public function leafDescendants()
    {
        $leaves = collect();

        $this->loadMissing('children');

        foreach ($this->children as $child) {

            $child->loadMissing('children');

            if ($child->children->isEmpty()) {
                $leaves->push($child);
            } else {
                $leaves = $leaves->merge($child->leafDescendants());
            }
        }

        return $leaves;
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }


    public function toSectionArray(): array
    {
        return [
            'id'       => $this->id,
            'title'     => $this->name,
            'desc'     => $this->description,
            'image'    => $this->icon,
            'price' => null,
            'discount' => null,
            'top_badges' => [],
            'bottom_badges' => [],
        ];
    }
}
