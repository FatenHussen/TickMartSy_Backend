<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class VendorService extends Model
{
    use HasTranslations;

    protected $table = 'vendor_services';

    public array $translatable = ['name', 'description'];

    protected $fillable = ['vendor_service_type_id', 'name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function type()
    {
        return $this->belongsTo(VendorServiceType::class, 'vendor_service_type_id');
    }
}
