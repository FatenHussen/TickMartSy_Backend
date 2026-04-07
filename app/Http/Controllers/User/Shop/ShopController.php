<?php

namespace App\Http\Controllers\User\Shop;

use App\Http\Controllers\BaseIndexController;
use App\Http\Requests\User\Shop\ShopRequest;
use App\Models\Shop;
use App\Services\User\ShopService;

class ShopController extends BaseIndexController
{
    public function __construct(ShopService $service)
    {
        $this->service = $service;
        $this->filterRequest = ShopRequest::class;
    }

    /**
     * Get all vendor services for a specific shop
     */
    public function services(int $shopId)
    {
        $shop = Shop::findOrFail($shopId);

        $services = $shop->vendorServices()
            ->with('vendorService.type')
            ->where('is_active', true)
            ->get()
            ->map(fn($sv) => [
                'id'               => $sv->id,
                'service'          => [
                    'id'          => $sv->vendorService->id,
                    'name'        => $sv->vendorService->name,
                    'description' => $sv->vendorService->description,
                    'type'        => [
                        'id'   => $sv->vendorService->type->id,
                        'name' => $sv->vendorService->type->name,
                    ],
                ],
                'price'            => $sv->price,
                'price_unit'       => $sv->price_unit,
                'duration_minutes' => $sv->duration_minutes,
                'extra_details'    => $sv->extra_details,
                'schedule'         => $sv->schedule,
                'is_open_now'      => $sv->isOpenNow(),
            ]);

        return $this->sendResponse($services);
    }
}
