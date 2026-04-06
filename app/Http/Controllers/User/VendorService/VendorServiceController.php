<?php

namespace App\Http\Controllers\User\VendorService;

use App\Http\Controllers\Controller;
use App\Models\VendorService;
use App\Models\VendorServiceType;

class VendorServiceController extends Controller
{
    /**
     * Get all active vendor services grouped by type
     */
    public function index()
    {
        $types = VendorServiceType::with(['vendorServices' => fn($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->get()
            ->map(fn($type) => [
                'id'       => $type->id,
                'name'     => $type->name,
                'services' => $type->vendorServices->map(fn($s) => [
                    'id'          => $s->id,
                    'name'        => $s->name,
                    'description' => $s->description,
                ]),
            ]);

        return $this->sendResponse($types);
    }
}
