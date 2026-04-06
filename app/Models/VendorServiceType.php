<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class VendorServiceType extends Model
{
    use HasTranslations;

    protected $table = 'vendor_service_types';

    public array $translatable = ['name'];

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function vendorServices()
    {
        return $this->hasMany(VendorService::class, 'vendor_service_type_id');
    }
}
