<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements Sectionable
{
    use HasFactory, HasTranslations, LogsActivity;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'is_restaurant',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_restaurant' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function activeChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('order', 'asc');
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
    public function  getImageUrlAttribute()
    {
        return asset('storage/' . $this->icon);
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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }


    public function toSectionArray(): array
    {
        return [
            'id'       => $this->id,
            'title'     => $this->name,
            'desc'     => null,
            'image'    => $this->icon,
            'price' => null,
            'discount' => null,
        ];
    }
}
